<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Contracts\Paco\PacoModelGateway;
use App\Data\Paco\AnalysisResult;
use App\Data\Paco\EvidencePlan;
use App\Data\Paco\TurnInterpretation;
use App\Models\Paco\Intent;
use App\Models\Paco\Question;
use App\Support\Paco\PacoTrace;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class OpenCodeGoModelGateway implements PacoModelGateway
{
    private bool $fallback = false;

    public function __construct(private readonly DeterministicPacoModelGateway $deterministic) {}

    /** @param array<string, mixed> $context */
    public function analyze(string $message, ?string $campaignIntent = null, array $context = []): AnalysisResult
    {
        $this->fallback = false;
        PacoTrace::debug('analyze.start', [
            'input_length' => mb_strlen($message),
            'campaign_intent' => $campaignIntent,
        ]);

        if (! $this->configured() || $this->circuitOpen()) {
            return $this->fallbackToDeterministic($message, $campaignIntent, $context);
        }

        try {
            $response = $this->client()->post('/chat/completions', [
                'model' => (string) config('paco.opencode_go.model'),
                'temperature' => 0,
                'max_tokens' => (int) config('paco.opencode_go.max_tokens'),
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $this->analysisInput($message, $context)],
                ],
            ]);

            if ($this->isUsageLimit($response->status(), $response->json())) {
                $this->openCircuit('usage_limit');

                return $this->fallbackToDeterministic($message, $campaignIntent, $context);
            }

            if ($response->failed()) {
                Log::warning('Paco OpenCode Go request failed; using deterministic fallback.', [
                    'status' => $response->status(),
                ]);

                return $this->fallbackToDeterministic($message, $campaignIntent, $context);
            }

            $result = $this->parse($response->json('choices.0.message.content'));
            if (! $result) {
                Log::warning('Paco OpenCode Go returned invalid structured output; using deterministic fallback.');

                return $this->fallbackToDeterministic($message, $campaignIntent, $context);
            }

            $result = $this->rescueWeakAnalysis($result, $message, $campaignIntent, $context);

            Cache::forget($this->circuitKey());
            PacoTrace::debug('analyze.result', [
                'provider' => 'opencode-go',
                'intent' => $result->primaryIntent,
                'confidence' => $result->confidence,
                'facts_count' => count($result->facts),
                'question_priorities' => $result->questionPriorities,
            ]);

            return $result;
        } catch (Throwable $exception) {
            Log::warning('Paco OpenCode Go request errored; using deterministic fallback.', [
                'exception' => $exception::class,
            ]);

            return $this->fallbackToDeterministic($message, $campaignIntent, $context);
        }
    }

    /** @param array<string, mixed> $context */
    public function interpretTurn(string $message, array $context): TurnInterpretation
    {
        $this->fallback = false;
        PacoTrace::debug('turn_router.start', [
            'input_length' => mb_strlen($message),
            'question' => data_get($context, 'active_question.code'),
        ]);

        if (! $this->configured() || $this->circuitOpen()) {
            return $this->fallbackTurnInterpretation($message, $context);
        }

        try {
            $contextJson = json_encode(
                $context,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            );
            $response = $this->client()->post('/chat/completions', [
                'model' => (string) config('paco.opencode_go.model'),
                'temperature' => 0,
                'max_tokens' => (int) config('paco.opencode_go.max_tokens'),
                'messages' => [
                    ['role' => 'system', 'content' => $this->turnInterpreterPrompt()],
                    ['role' => 'user', 'content' => "<authorized_context>\n{$contextJson}\n</authorized_context>\n<visitor_message>\n{$message}\n</visitor_message>"],
                ],
            ]);

            if ($this->isUsageLimit($response->status(), $response->json())) {
                $this->openCircuit('usage_limit');

                return $this->fallbackTurnInterpretation($message, $context);
            }

            if ($response->failed()) {
                Log::warning('Paco turn interpretation request failed; using deterministic fallback.', [
                    'status' => $response->status(),
                ]);

                return $this->fallbackTurnInterpretation($message, $context);
            }

            $result = $this->parseTurnInterpretation($response->json('choices.0.message.content'));
            if (! $result) {
                Log::warning('Paco turn interpretation returned invalid structured output; using deterministic fallback.');

                return $this->fallbackTurnInterpretation($message, $context);
            }

            if ($result->disposition === 'low_information') {
                $deterministic = $this->deterministic->interpretTurn($message, $context);
                if ($deterministic->answersCurrentQuestion) {
                    $result = $deterministic;
                }
            }

            Cache::forget($this->circuitKey());
            PacoTrace::debug('turn_router.result', [
                'provider' => 'opencode-go',
                'disposition' => $result->disposition,
                'answers_current_question' => $result->answersCurrentQuestion,
                'useful' => $result->useful,
                'confidence' => $result->confidence,
            ]);

            return $result;
        } catch (Throwable $exception) {
            Log::warning('Paco turn interpretation errored; using deterministic fallback.', [
                'exception' => $exception::class,
            ]);

            return $this->fallbackTurnInterpretation($message, $context);
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array<string, mixed>>  $candidates
     */
    public function planEvidence(array $context, array $candidates): EvidencePlan
    {
        $this->fallback = false;

        if ($candidates === [] || ! $this->configured() || $this->circuitOpen()) {
            return $this->fallbackEvidencePlan($context, $candidates);
        }

        try {
            $payload = json_encode([
                'conversation' => $context,
                'authorized_evidence' => $candidates,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $response = $this->client()->post('/chat/completions', [
                'model' => (string) config('paco.opencode_go.model'),
                'temperature' => 0,
                'max_tokens' => min(900, (int) config('paco.opencode_go.max_tokens')),
                'messages' => [
                    ['role' => 'system', 'content' => $this->evidencePlannerPrompt()],
                    ['role' => 'user', 'content' => "<commercial_context>\n{$payload}\n</commercial_context>"],
                ],
            ]);

            if ($this->isUsageLimit($response->status(), $response->json())) {
                $this->openCircuit('usage_limit');

                return $this->fallbackEvidencePlan($context, $candidates);
            }

            if ($response->failed()) {
                return $this->fallbackEvidencePlan($context, $candidates);
            }

            $plan = $this->parseEvidencePlan($response->json('choices.0.message.content'), $candidates);
            if (! $plan) {
                return $this->fallbackEvidencePlan($context, $candidates);
            }

            Cache::forget($this->circuitKey());
            PacoTrace::debug('evidence_planner.result', [
                'provider' => 'opencode-go',
                'selected_item_ids' => $plan->selectedItemIds,
                'relationship' => $plan->relationship,
                'has_testimonial' => $plan->testimonialItemId !== null,
            ]);

            return $plan;
        } catch (Throwable $exception) {
            Log::warning('Paco evidence planning errored; using deterministic fallback.', [
                'exception' => $exception::class,
            ]);

            return $this->fallbackEvidencePlan($context, $candidates);
        }
    }

    public function provider(): string
    {
        return $this->fallback ? 'application-fallback' : 'opencode-go';
    }

    public function model(): string
    {
        return $this->fallback ? $this->deterministic->model() : (string) config('paco.opencode_go.model');
    }

    public function usedFallback(): bool
    {
        return $this->fallback;
    }

    private function configured(): bool
    {
        return filled(config('paco.opencode_go.api_key'));
    }

    private function circuitOpen(): bool
    {
        return Cache::has($this->circuitKey());
    }

    private function openCircuit(string $reason): void
    {
        if (! config('paco.fallback.enabled')) {
            return;
        }

        Cache::put(
            $this->circuitKey(),
            ['reason' => $reason, 'opened_at' => now()->toIso8601String()],
            now()->addMinutes((int) config('paco.fallback.cooldown_minutes')),
        );
    }

    private function circuitKey(): string
    {
        return 'paco:opencode-go:circuit-open';
    }

    /** @param array<string, mixed> $context */
    private function fallbackToDeterministic(string $message, ?string $campaignIntent, array $context = []): AnalysisResult
    {
        $this->fallback = true;

        return $this->deterministic->analyze($message, $campaignIntent, $context);
    }

    /** @param array<string, mixed> $context */
    private function rescueWeakAnalysis(
        AnalysisResult $result,
        string $message,
        ?string $campaignIntent,
        array $context,
    ): AnalysisResult {
        $deterministic = $this->deterministic->analyze($message, $campaignIntent, $context);
        $needsRescue = $deterministic->primaryIntent !== 'general'
            && ($result->primaryIntent === 'general' || $result->confidence < (float) config('paco.min_intent_confidence'));

        if (! $needsRescue) {
            return $result;
        }

        return new AnalysisResult(
            primaryIntent: $deterministic->primaryIntent,
            confidence: max($result->confidence, $deterministic->confidence),
            facts: $result->facts !== [] ? $result->facts : $deterministic->facts,
            questionPriorities: $result->questionPriorities,
            acknowledgement: $result->acknowledgement,
        );
    }

    /** @param array<string, mixed> $context */
    private function analysisInput(string $message, array $context): string
    {
        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';

        return "<conversation_context>\n{$contextJson}\n</conversation_context>\n<visitor_message>\n{$message}\n</visitor_message>";
    }

    /** @param array<string, mixed> $context */
    private function fallbackTurnInterpretation(string $message, array $context): TurnInterpretation
    {
        $this->fallback = true;

        return $this->deterministic->interpretTurn($message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array<string, mixed>>  $candidates
     */
    private function fallbackEvidencePlan(array $context, array $candidates): EvidencePlan
    {
        $this->fallback = true;

        return $this->deterministic->planEvidence($context, $candidates);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('paco.opencode_go.base_url'), '/'))
            ->withToken((string) config('paco.opencode_go.api_key'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('paco.opencode_go.timeout_seconds'))
            ->connectTimeout((int) config('paco.opencode_go.connect_timeout_seconds'));
    }

    /** @param array<string, mixed>|null $payload */
    private function isUsageLimit(int $status, ?array $payload): bool
    {
        if (in_array($status, [402, 429], true)) {
            return true;
        }

        $text = Str::lower(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');

        foreach (['rate limit', 'rate_limit', 'quota', 'insufficient_quota', 'usage limit', 'credits exhausted'] as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function systemPrompt(): string
    {
        $intents = Intent::query()->where('status', 'active')->orderBy('code')->pluck('code')->implode(', ');
        $questions = Question::query()
            ->where('status', 'active')
            ->whereNotIn('code', ['initial_need', 'contact'])
            ->orderBy('code')
            ->get(['code', 'prompt'])
            ->map(fn ($question): string => "{$question->code}: {$question->prompt}")
            ->implode("\n");

        return <<<PROMPT
Sos el analizador y planificador estructurado de Socies. Analizá el mensaje del visitante sin responderle directamente.
Devolvé únicamente JSON válido, sin markdown, con esta forma exacta:
{"primary_intent":"...","confidence":0.0,"facts":[{"field":"...","value":"...","confidence":0.0,"evidence":"..."}],"question_priorities":["..."],"acknowledgement":"..."}

Elegí primary_intent únicamente de esta lista: {$intents}.
Confidence debe ser un número entre 0 y 1. Facts debe ser un array vacío si no hay datos claros.
No inventes datos. La evidencia debe ser una frase breve tomada del mensaje.
El contexto puede incluir `previous_need`: usalo cuando el visitante diga “ya te dije”, corrija o haga referencia a su mensaje anterior. No afirmes que no recordás la conversación.
Ordená question_priorities con hasta 6 códigos de preguntas que más ayudarían a entender este caso. Usá únicamente códigos del catálogo siguiente. El backend validará vigencia, sensibilidad y relevancia contextual antes de mostrarlas.
Priorizá preguntas específicas del caso sobre datos administrativos. Dejá presupuesto para el final y proponé organization_name solo si el mensaje habla de una organización, empresa o equipo.
Redactá acknowledgement como una sola oración breve, de 4 a 18 palabras, confirmando únicamente lo entendido. Preferí “Buscan…”, “Querés…” o “Entendemos que…”. No uses “Tenemos…” para describir el proyecto del visitante. Hablá como "nosotros", sin elogios, promesas, precios ni frases genéricas como "Entendimos". Usá null si el mensaje no tiene información suficiente.

Catálogo de preguntas:
{$questions}
PROMPT;
    }

    private function turnInterpreterPrompt(): string
    {
        return <<<'PROMPT'
Sos el enrutador de un pipeline comercial controlado. Clasificá el mensaje del visitante respecto de la pregunta activa.
Devolvé únicamente JSON válido, sin markdown, con esta forma exacta:
{"disposition":"answer|question|correction|objection|low_information|off_topic","answers_current_question":true,"useful":true,"confidence":0.0,"normalized_answer":null,"reply":null}

Reglas:
- answer: responde de manera relevante la pregunta activa. Solo en este caso answers_current_question y useful pueden ser true.
- question: el visitante pide una explicación o hace una pregunta, aunque use pocas palabras.
- correction: corrige información anterior y no responde la pregunta activa.
- objection: expresa frustración, por ejemplo que no recibió respuesta.
- low_information: signos, texto ininteligible o contenido que no permite completar el campo.
- off_topic: contenido claramente ajeno a la conversación.
- No trates una pregunta, objeción o texto ininteligible como respuesta al campo.
- normalized_answer conserva el significado del visitante y solo se completa para answer.
- Para question, correction u objection, reply debe responder o reconocer el mensaje en 1 a 3 oraciones breves en español rioplatense. No incluyas la pregunta activa: el backend la agregará.
- Usá solamente el contexto autorizado. No inventes capacidades de Socies, precios, plazos, clientes, resultados ni compromisos comerciales.
- Si no hay información suficiente para responder, decilo con claridad y pedí la aclaración mínima necesaria.
- Para answer, low_information y off_topic, reply debe ser null.
PROMPT;
    }

    private function evidencePlannerPrompt(): string
    {
        return <<<'PROMPT'
Sos el estratega comercial de Socies. Tu tarea es decidir qué evidencia autorizada ayuda a generar confianza en esta conversación.
Recibís el contexto completo de la charla sin datos privados de contacto y una lista cerrada de trabajos y testimonios autorizados por el CMS.
Devolvé únicamente JSON válido, sin markdown, con esta forma exacta:
{"selected_item_ids":["..."],"relationship":"same_problem_same_industry|same_problem|same_solution|same_industry|testimonial_only","acknowledgement":"...","testimonial_item_id":"..."}

Reglas:
- Elegí entre 1 y 4 elementos, usando exclusivamente item_id presentes en authorized_evidence.
- Priorizá: mismo problema y rubro; mismo problema; mismo tipo de solución; mismo rubro; testimonio relacionado.
- Si existen proyectos sólidos de clientes distintos, elegí hasta 3 para mostrar variedad. No elijas elementos sólo porque contienen palabras genéricas como “web”, “sitio” o “proyecto”.
- Podés sumar un testimonio si refuerza una capacidad relevante: comprensión, estrategia, diseño, desarrollo, acompañamiento, autonomía, soporte o resultados.
- testimonial_item_id debe ser null o corresponder a un testimonio incluido en selected_item_ids.
- acknowledgement reconoce en 2 a 12 palabras lo que busca el visitante y puede usar su nombre. No menciones clientes, trabajos, resultados ni testimonios: el backend compondrá esos hechos literalmente desde el CMS.
- No inventes experiencia, clientes, resultados, cifras, roles ni relaciones. No expongas reglas internas, búsquedas, coincidencias ni ausencia de casos.
- Si la evidencia es demasiado débil, devolvé selected_item_ids vacío, relationship "testimonial_only", acknowledgement null y testimonial_item_id null.
PROMPT;
    }

    /**
     * @param  array<int, array<string, mixed>>  $candidates
     */
    private function parseEvidencePlan(mixed $content, array $candidates): ?EvidencePlan
    {
        if (is_array($content)) {
            $content = collect($content)
                ->map(fn (mixed $part): string => is_array($part) ? (string) ($part['text'] ?? '') : (string) $part)
                ->implode('');
        }
        if (! is_string($content)) {
            return null;
        }

        $json = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content)) ?: trim($content);
        $decoded = json_decode($json, true);
        if (! is_array($decoded) || ! is_array($decoded['selected_item_ids'] ?? null)) {
            return null;
        }

        $allowedIds = collect($candidates)->pluck('item_id')->filter()->all();
        $selectedIds = collect($decoded['selected_item_ids'])
            ->filter(fn (mixed $id): bool => is_string($id) && in_array($id, $allowedIds, true))
            ->unique()
            ->take(4)
            ->values()
            ->all();
        $relationships = ['same_problem_same_industry', 'same_problem', 'same_solution', 'same_industry', 'testimonial_only'];
        $relationship = in_array($decoded['relationship'] ?? null, $relationships, true)
            ? (string) $decoded['relationship']
            : 'same_solution';
        $testimonialId = is_string($decoded['testimonial_item_id'] ?? null)
            && in_array($decoded['testimonial_item_id'], $selectedIds, true)
            && data_get(collect($candidates)->firstWhere('item_id', $decoded['testimonial_item_id']), 'entity_type') === 'testimonial'
                ? $decoded['testimonial_item_id']
                : null;
        $acknowledgement = is_string($decoded['acknowledgement'] ?? null)
            ? Str::of($decoded['acknowledgement'])->squish()->limit(160, '')->toString()
            : null;

        return new EvidencePlan(
            selectedItemIds: $selectedIds,
            relationship: $relationship,
            acknowledgement: filled($acknowledgement) ? $acknowledgement : null,
            testimonialItemId: $testimonialId,
        );
    }

    private function parseTurnInterpretation(mixed $content): ?TurnInterpretation
    {
        if (is_array($content)) {
            $content = collect($content)
                ->map(fn (mixed $part): string => is_array($part) ? (string) ($part['text'] ?? '') : (string) $part)
                ->implode('');
        }

        if (! is_string($content)) {
            return null;
        }

        $json = trim($content);
        $json = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $json) ?: $json;
        $decoded = json_decode($json, true);
        $allowed = ['answer', 'question', 'correction', 'objection', 'low_information', 'off_topic'];

        if (! is_array($decoded) || ! in_array($decoded['disposition'] ?? null, $allowed, true)) {
            return null;
        }

        $disposition = (string) $decoded['disposition'];
        $isAnswer = $disposition === 'answer';
        $normalizedAnswer = $isAnswer && is_string($decoded['normalized_answer'] ?? null)
            ? Str::of($decoded['normalized_answer'])->squish()->limit((int) config('paco.max_message_length'), '')->toString()
            : null;
        $reply = ! $isAnswer && is_string($decoded['reply'] ?? null)
            ? Str::of($decoded['reply'])->squish()->limit(600, '')->toString()
            : null;

        if ($isAnswer && mb_strlen((string) $normalizedAnswer) < 3) {
            return null;
        }

        return new TurnInterpretation(
            disposition: $disposition,
            answersCurrentQuestion: $isAnswer && (bool) ($decoded['answers_current_question'] ?? false),
            useful: $isAnswer && (bool) ($decoded['useful'] ?? false),
            confidence: max(0, min(1, (float) ($decoded['confidence'] ?? 0))),
            normalizedAnswer: $normalizedAnswer,
            reply: filled($reply) ? $reply : null,
        );
    }

    private function parse(mixed $content): ?AnalysisResult
    {
        if (is_array($content)) {
            $content = collect($content)->map(fn (mixed $part): string => is_array($part) ? (string) ($part['text'] ?? '') : (string) $part)->implode('');
        }

        if (! is_string($content)) {
            return null;
        }

        $json = trim($content);
        $json = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $json) ?: $json;
        $decoded = json_decode($json, true);

        if (! is_array($decoded) || ! is_string($decoded['primary_intent'] ?? null)) {
            return null;
        }

        $intentExists = Intent::query()->where('status', 'active')->where('code', $decoded['primary_intent'])->exists();
        if (! $intentExists) {
            return null;
        }

        $facts = [];
        foreach (is_array($decoded['facts'] ?? null) ? $decoded['facts'] : [] as $fact) {
            if (! is_array($fact) || ! isset($fact['field'], $fact['evidence'])) {
                continue;
            }
            $field = Str::snake(Str::ascii((string) $fact['field']));
            if (! preg_match('/^[a-z][a-z0-9_]{1,99}$/', $field)) {
                continue;
            }
            $facts[] = [
                'field' => $field,
                'value' => is_scalar($fact['value'] ?? null) ? $fact['value'] : null,
                'confidence' => max(0, min(1, (float) ($fact['confidence'] ?? 0))),
                'evidence' => Str::limit((string) $fact['evidence'], 300, ''),
            ];
        }

        $allowedQuestions = Question::query()
            ->where('status', 'active')
            ->pluck('code')
            ->all();
        $questionPriorities = collect(is_array($decoded['question_priorities'] ?? null) ? $decoded['question_priorities'] : [])
            ->filter(fn (mixed $code): bool => is_string($code) && in_array($code, $allowedQuestions, true))
            ->unique()
            ->take(6)
            ->values()
            ->all();
        $acknowledgement = is_string($decoded['acknowledgement'] ?? null)
            ? Str::of($decoded['acknowledgement'])->squish()->limit(180, '')->toString()
            : null;

        return new AnalysisResult(
            primaryIntent: $decoded['primary_intent'],
            confidence: max(0, min(1, (float) ($decoded['confidence'] ?? 0))),
            facts: $facts,
            questionPriorities: $questionPriorities,
            acknowledgement: filled($acknowledgement) ? $acknowledgement : null,
        );
    }
}
