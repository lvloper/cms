<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Contracts\Paco\PacoModelGateway;
use App\Data\Paco\ActionResult;
use App\Data\Paco\AnalysisResult;
use App\Data\Paco\TurnInterpretation;
use App\Enums\Paco\ConversationStage;
use App\Enums\Paco\ConversationStatus;
use App\Enums\Paco\FitStatus;
use App\Jobs\Paco\SendLeadNotification;
use App\Models\Paco\Campaign;
use App\Models\Paco\ContentImpression;
use App\Models\Paco\Conversation;
use App\Models\Paco\ConversationEvent;
use App\Models\Paco\Lead;
use App\Models\Paco\ModelRun;
use App\Models\Paco\Question;
use App\Models\Paco\ResponseBlock;
use App\Support\Paco\PacoTrace;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class PacoConversationService
{
    public function __construct(
        private readonly PacoModelGateway $model,
        private readonly PacoTokenService $tokens,
        private readonly PacoFitResolver $fitResolver,
        private readonly PacoPlaybookResolver $playbookResolver,
        private readonly PacoQuestionSelector $questionSelector,
        private readonly PacoSufficiencyEvaluator $sufficiencyEvaluator,
        private readonly PacoCommercialContextEvaluator $commercialContext,
        private readonly PacoTurnFactory $turns,
        private readonly PacoEvidenceRetriever $evidenceRetriever,
    ) {}

    /** @param array<string, mixed> $data
     * @param  array<string, mixed>  $clientContext
     * @return array{conversation: Conversation, token: string, prefill: array<string, mixed>|null}
     */
    public function create(array $data, array $clientContext): array
    {
        $requestedCampaign = (string) ($data['campaign'] ?? config('paco.default_campaign'));
        $campaign = Campaign::query()->available()->where('code', $requestedCampaign)->first()
            ?? Campaign::query()->available()->where('code', config('paco.default_campaign'))->firstOrFail();

        $this->assertOriginAllowed($campaign, $clientContext['origin_host'] ?? null);

        $prefill = $this->consumePrefill($data['prefill_token'] ?? null, $campaign);
        $token = $this->tokens->issue();

        $conversation = DB::transaction(function () use ($campaign, $clientContext, $data, $token): Conversation {
            $conversation = Conversation::query()->create([
                'public_token_hash' => $this->tokens->hash($token),
                'campaign_id' => $campaign->id,
                'playbook_id' => $campaign->preferred_playbook_id,
                'status' => ConversationStatus::Active,
                'stage' => ConversationStage::New,
                'locale' => $data['locale'] ?? 'es-AR',
                'origin_url' => $data['origin_url'] ?? null,
                'origin_host' => $clientContext['origin_host'] ?? null,
                'referrer' => $data['referrer'] ?? null,
                'utm_source' => $data['utm_source'] ?? null,
                'utm_medium' => $data['utm_medium'] ?? null,
                'utm_campaign' => $data['utm_campaign'] ?? null,
                'client_ip_hash' => $clientContext['ip_hash'] ?? null,
                'country_code' => $clientContext['country_code'] ?? null,
                'user_agent_summary' => $clientContext['user_agent'] ?? null,
                'version' => 1,
                'last_activity_at' => now(),
            ]);

            Lead::query()->create([
                'conversation_id' => $conversation->id,
                'status' => 'collecting',
                'country_code' => $clientContext['country_code'] ?? null,
                'state' => [
                    'answered_fields' => [],
                    'asked_questions' => [],
                    'contact_captured' => false,
                    'questions_after_contact' => 0,
                    'sufficiency' => 0,
                ],
            ]);

            $this->appendEvent($conversation, 'assistant', 'assistant_turn', [
                'turn' => $this->turns->initial($campaign->initial_message),
            ]);

            return $conversation;
        });

        return ['conversation' => $conversation->fresh(), 'token' => $token, 'prefill' => $prefill];
    }

    /** @param array<string, mixed> $action */
    public function act(Conversation $conversation, array $action, int $version, string $idempotencyKey): Conversation
    {
        return DB::transaction(function () use ($action, $conversation, $idempotencyKey, $version): Conversation {
            $locked = Conversation::query()->lockForUpdate()->findOrFail($conversation->id);

            if ($locked->events()->where('idempotency_key', $idempotencyKey)->exists()) {
                return $locked->fresh();
            }

            if ($locked->version !== $version) {
                throw new ConflictHttpException('La conversación cambió. Recargá el estado antes de continuar.');
            }

            if ($locked->status !== ConversationStatus::Active) {
                throw new ConflictHttpException('La conversación ya está cerrada.');
            }

            $lead = $locked->lead()->firstOrFail();
            $hadContact = (bool) (($lead->state ?? [])['contact_captured'] ?? false);
            $display = $this->displayValue($action);
            $event = $this->appendEvent($locked, 'user', 'component_submit', [
                'action' => $action,
                'display' => $display,
            ], $idempotencyKey);

            $result = $this->applyAction($locked, $lead, $event, $action);

            if ($hadContact && $result->accepted) {
                $state = $lead->state ?? [];
                $state['questions_after_contact'] = ((int) ($state['questions_after_contact'] ?? 0)) + 1;
                $lead->state = $state;
                $lead->save();
            }

            $locked->increment('interaction_count');
            if ($result->useful) {
                $locked->increment('useful_interaction_count');
            }
            $locked->refresh();

            $turn = $result->turn && ! $this->reachedInteractionLimit($locked)
                ? $result->turn
                : $this->nextTurn($locked, $lead->fresh());
            $assistantEvent = $this->appendEvent($locked, 'assistant', 'assistant_turn', ['turn' => $turn]);
            $this->recordContentImpressions($locked, $assistantEvent, $turn);

            $locked->forceFill([
                'version' => $locked->version + 1,
                'last_activity_at' => now(),
            ])->save();

            return $locked->fresh(['events', 'campaign']);
        }, 3);
    }

    /** @param array<string, mixed> $action */
    private function applyAction(
        Conversation $conversation,
        Lead $lead,
        ConversationEvent $event,
        array $action,
    ): ActionResult {
        $type = (string) $action['type'];

        if ($type === 'confirm_prefill') {
            $values = (array) ($action['values'] ?? []);
            $isUseful = $this->applyInitialNeed($conversation, $lead, $event, (string) ($values['initial_query'] ?? ''));
            $this->applyContact($lead, $event, $values);

            return ActionResult::accepted($isUseful);
        }

        if ($type === 'contact_submit') {
            $this->applyContact($lead, $event, (array) ($action['value'] ?? []));

            return ActionResult::accepted();
        }

        $componentId = (string) ($action['component_id'] ?? '');
        $value = $action['value'] ?? null;

        if ($componentId === 'initial_need') {
            $isUseful = $this->applyInitialNeed($conversation, $lead, $event, (string) $value);

            return new ActionResult(useful: $isUseful, accepted: $isUseful);
        }

        $question = Question::query()->where('code', $componentId)->first();
        if (! $question) {
            throw ValidationException::withMessages(['action.component_id' => 'El componente no está permitido.']);
        }

        if ($question->component_type === 'text_input') {
            if (! is_string($value)) {
                throw ValidationException::withMessages(['action.value' => 'Escribí una respuesta válida.']);
            }

            $interpretation = $this->interpretQuestionAnswer($conversation, $lead, $question, $value);
            if (! $interpretation->answersCurrentQuestion || ! $interpretation->useful) {
                $prefix = $interpretation->reply ?? match ($interpretation->disposition) {
                    'low_information' => 'No llegamos a interpretar esa respuesta. Contanos un poco más.',
                    'off_topic' => 'Volvamos a la consulta para poder orientarte.',
                    default => 'Antes de avanzar, retomemos lo que nos planteaste.',
                };

                return ActionResult::reroute($this->turns->question($question, $prefix));
            }

            $value = $interpretation->normalizedAnswer ?? $value;
        }

        $value = $this->normalizeQuestionValue($question, $value);

        $this->setAttribute($lead, $question->field_code, $value, $event, 'explicit');

        $state = $lead->state ?? [];
        $state['answered_fields'][$question->field_code] = $value;
        $lead->state = $state;

        $leadColumn = match ($question->field_code) {
            'organization_name', 'decision_role', 'deadline' => $question->field_code,
            default => null,
        };
        if ($leadColumn) {
            $lead->{$leadColumn} = $value === 'skip' ? null : $value;
        }
        $lead->save();

        return ActionResult::accepted();
    }

    private function interpretQuestionAnswer(
        Conversation $conversation,
        Lead $lead,
        Question $question,
        string $message,
    ): TurnInterpretation {
        $message = Str::of($message)
            ->squish()
            ->limit((int) config('paco.max_message_length'), '')
            ->toString();
        $lastAssistantPayload = $conversation->events()
            ->where('actor', 'assistant')
            ->orderByDesc('sequence')
            ->first()?->payload;
        $context = [
            'conversation_stage' => $conversation->stage->value,
            'primary_intent' => $lead->primary_intent_code,
            'problem_summary' => $lead->problem_summary,
            'active_question' => [
                'code' => $question->code,
                'field' => $question->field_code,
                'prompt' => $question->prompt,
            ],
            'last_assistant_message' => data_get($lastAssistantPayload, 'turn.message'),
        ];
        $startedAt = hrtime(true);
        $interpretation = $this->model->interpretTurn($message, $context);
        $latency = (int) round((hrtime(true) - $startedAt) / 1_000_000);
        PacoTrace::debug('turn_router.decision', [
            'conversation_id' => $conversation->id,
            'question' => $question->code,
            'disposition' => $interpretation->disposition,
            'answers_current_question' => $interpretation->answersCurrentQuestion,
            'useful' => $interpretation->useful,
            'confidence' => $interpretation->confidence,
            'latency_ms' => $latency,
        ]);

        ModelRun::query()->create([
            'conversation_id' => $conversation->id,
            'phase' => 'turn_router',
            'provider' => $this->model->provider(),
            'model' => $this->model->model(),
            'prompt_version' => 'turn-router-v1',
            'input_hash' => hash('sha256', $message),
            'input_snapshot' => [
                'length' => mb_strlen($message),
                'question_code' => $question->code,
            ],
            'output_snapshot' => $interpretation->toArray(),
            'validated' => true,
            'latency_ms' => $latency,
        ]);

        return $interpretation;
    }

    private function applyInitialNeed(
        Conversation $conversation,
        Lead $lead,
        ConversationEvent $event,
        string $message,
    ): bool {
        $message = Str::of($message)->squish()->limit((int) config('paco.max_message_length'), '')->toString();
        if (mb_strlen($message) < 3) {
            throw ValidationException::withMessages(['action.value' => 'Contanos un poco más sobre la consulta.']);
        }

        $campaignIntent = $conversation->campaign?->intent?->code;
        $startedAt = hrtime(true);
        $analysisContext = $this->initialNeedContext($conversation, $event);
        $analysis = $this->model->analyze($message, $campaignIntent, $analysisContext);
        $latency = (int) round((hrtime(true) - $startedAt) / 1_000_000);
        PacoTrace::debug('analyzer.decision', [
            'conversation_id' => $conversation->id,
            'intent' => $analysis->primaryIntent,
            'confidence' => $analysis->confidence,
            'facts_count' => count($analysis->facts),
            'question_priorities' => $analysis->questionPriorities,
            'latency_ms' => $latency,
        ]);

        ModelRun::query()->create([
            'conversation_id' => $conversation->id,
            'phase' => 'analyzer',
            'provider' => $this->model->provider(),
            'model' => $this->model->model(),
            'prompt_version' => $this->model->model(),
            'input_hash' => hash('sha256', $message),
            'input_snapshot' => [
                'length' => mb_strlen($message),
                'has_previous_need' => filled($analysisContext['previous_need'] ?? null),
            ],
            'output_snapshot' => $analysis->toArray(),
            'validated' => true,
            'latency_ms' => $latency,
        ]);

        if ($this->needsClarification($analysis)) {
            $state = $lead->state ?? [];
            $state['clarification_attempts'] = ((int) ($state['clarification_attempts'] ?? 0)) + 1;
            $lead->state = $state;
            $lead->save();
            $conversation->stage = ConversationStage::UnderstandingNeed;
            $conversation->save();

            return false;
        }

        $playbook = $this->playbookResolver->resolve($analysis->primaryIntent, $conversation->campaign()->firstOrFail());
        $fit = $this->fitResolver->resolve($analysis->primaryIntent);

        $conversation->playbook_id = $playbook->id;
        $conversation->stage = ConversationStage::UnderstandingNeed;
        $conversation->save();

        $state = $lead->state ?? [];
        $state['answered_fields']['problem_summary'] = $message;
        $state['fit_status'] = $fit->value;
        $state['question_priority_codes'] = $analysis->questionPriorities;
        $state['initial_acknowledgement'] = $analysis->acknowledgement;
        $lead->forceFill([
            'problem_summary' => $message,
            'primary_intent_code' => $analysis->primaryIntent,
            'fit_level' => $fit->value,
            'state' => $state,
        ])->save();

        $this->setAttribute($lead, 'problem_summary', $message, $event, 'explicit');
        $this->setAttribute($lead, 'primary_intent', $analysis->primaryIntent, $event, 'inferred', $analysis->confidence);
        foreach ($analysis->facts as $fact) {
            if ($fact['field'] === 'primary_intent' || $fact['confidence'] < 0.6) {
                continue;
            }
            $this->setAttribute(
                $lead,
                $fact['field'],
                $fact['value'],
                $event,
                'inferred',
                $fact['confidence'],
                $fact['evidence'],
            );
        }

        return true;
    }

    private function needsClarification(AnalysisResult $analysis): bool
    {
        return $analysis->primaryIntent === 'general'
            && $analysis->confidence < (float) config('paco.min_intent_confidence')
            && $analysis->facts === [];
    }

    /** @return array<string, mixed> */
    private function initialNeedContext(Conversation $conversation, ConversationEvent $currentEvent): array
    {
        $previousNeed = $conversation->events()
            ->where('actor', 'user')
            ->where('kind', 'component_submit')
            ->where('sequence', '<', $currentEvent->sequence)
            ->orderByDesc('sequence')
            ->get()
            ->map(fn (ConversationEvent $event): mixed => data_get($event->payload, 'action.component_id') === 'initial_need'
                ? data_get($event->payload, 'action.value')
                : null)
            ->first(fn (mixed $value): bool => is_string($value) && mb_strlen(Str::squish($value)) >= 3);

        return filled($previousNeed) ? ['previous_need' => Str::squish((string) $previousNeed)] : [];
    }

    /** @param array<string, mixed> $values */
    private function applyContact(Lead $lead, ConversationEvent $event, array $values): void
    {
        $name = Str::of((string) ($values['name'] ?? ''))->squish()->toString();
        $channel = (string) ($values['channel'] ?? $values['contact_channel'] ?? '');
        $contact = trim((string) ($values['contact_value'] ?? ''));

        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors['action.value.name'] = 'Ingresá tu nombre.';
        }
        if (! in_array($channel, ['email', 'whatsapp'], true)) {
            $errors['action.value.channel'] = 'Elegí email o WhatsApp.';
        } elseif ($channel === 'email' && ! filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $errors['action.value.contact_value'] = 'Ingresá un email válido.';
        } elseif ($channel === 'whatsapp' && ! preg_match('/^\+?[0-9][0-9\s().-]{7,24}$/', $contact)) {
            $errors['action.value.contact_value'] = 'Ingresá un número de WhatsApp válido.';
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $normalizedPhone = $channel === 'whatsapp' ? '+'.ltrim((string) preg_replace('/\D+/', '', $contact), '+') : null;
        $state = $lead->state ?? [];
        $state['contact_captured'] = true;
        $state['answered_fields']['contact'] = true;

        $lead->forceFill([
            'name' => $name,
            'contact_channel' => $channel,
            'email' => $channel === 'email' ? Str::lower($contact) : null,
            'phone_e164' => $normalizedPhone,
            'state' => $state,
        ])->save();

        $this->setAttribute($lead, 'name', $name, $event, 'explicit');
        $this->setAttribute($lead, 'contact_channel', $channel, $event, 'explicit');
    }

    /** @return array<string, mixed> */
    private function nextTurn(Conversation $conversation, Lead $lead): array
    {
        $state = $lead->state ?? [];
        $fit = FitStatus::tryFrom((string) ($state['fit_status'] ?? 'unknown')) ?? FitStatus::Unknown;

        if ($fit === FitStatus::Unsupported) {
            $message = ResponseBlock::query()->where('code', 'unsupported_default')->value('text')
                ?? 'Gracias por escribirnos. Esa consulta no forma parte de nuestros servicios actuales.';

            return $this->close($conversation, $lead, $message, 'close_unsupported', 'non_commercial');
        }

        if ($this->reachedInteractionLimit($conversation)) {
            $channel = $lead->contact_channel === 'whatsapp' ? 'WhatsApp' : 'email';
            $message = $lead->name
                ? "Gracias, {$lead->name}. Con lo que nos contaste ya podemos revisar el caso. Nuestro equipo te va a contactar por {$channel}."
                : 'Gracias por escribirnos. Si querés que revisemos el caso, iniciá una nueva conversación y dejanos un medio de contacto.';

            return $this->close(
                $conversation,
                $lead,
                $message,
                'close_limit',
                $lead->name ? 'pending_review' : 'abandoned',
            );
        }

        if (blank($lead->problem_summary)) {
            $conversation->stage = ConversationStage::UnderstandingNeed;
            $conversation->save();

            return $this->turns->clarifyNeed();
        }

        $hasContact = (bool) ($state['contact_captured'] ?? false);
        if (! $hasContact && $conversation->useful_interaction_count >= 2) {
            $conversation->stage = ConversationStage::ContactRequired;
            $conversation->save();

            return $this->turns->contact();
        }

        $playbook = $conversation->playbook()->first()
            ?? $this->playbookResolver->resolve($lead->primary_intent_code ?? 'general', $conversation->campaign()->firstOrFail());
        $evidence = null;
        $evidenceAttemptedNow = false;
        if ($hasContact
            && ! ($state['commercial_evidence_attempted'] ?? false)
            && $this->commercialContext->readyForEvidence($lead)) {
            $evidenceAttemptedNow = true;
            $startedAt = hrtime(true);
            $evidenceContext = $this->commercialEvidenceContext($conversation, $lead);
            $evidence = $this->evidenceRetriever->retrieve($lead, $evidenceContext);
            $latency = (int) round((hrtime(true) - $startedAt) / 1_000_000);
            if ($evidence !== null) {
                ModelRun::query()->create([
                    'conversation_id' => $conversation->id,
                    'phase' => 'evidence_composer',
                    'provider' => data_get($evidence, 'composition.provider', 'application'),
                    'model' => data_get($evidence, 'composition.model', 'deterministic-v1'),
                    'prompt_version' => 'evidence-composer-v1',
                    'input_hash' => hash('sha256', $conversation->id.'|'.($lead->problem_summary ?? '')),
                    'input_snapshot' => [
                        'conversation_turns' => count($evidenceContext['conversation'] ?? []),
                        'candidate_count' => data_get($evidence, 'composition.candidate_count', 0),
                    ],
                    'output_snapshot' => Arr::except((array) ($evidence['composition'] ?? []), ['provider', 'model']),
                    'validated' => true,
                    'latency_ms' => $latency,
                ]);
            }
            $state['commercial_evidence_attempted'] = true;
            $state['commercial_evidence_shown'] = $evidence !== null;
            $state['content_shown'] = $evidence
                ? collect($evidence['items'])->map(fn (array $item): array => [
                    'type' => $item['entity_type'],
                    'id' => $item['item_id'],
                ])->all()
                : [];
        }
        $sufficiency = $this->sufficiencyEvaluator->evaluate($playbook, $lead);
        $state['sufficiency'] = $sufficiency['score'];
        $state['missing_high_priority'] = $sufficiency['missing_high_priority'];
        $lead->state = $state;
        $lead->save();

        if ($sufficiency['sufficient']) {
            $channel = $lead->contact_channel === 'whatsapp' ? 'WhatsApp' : 'email';
            $message = $evidence
                ? $evidence['message']." Gracias, {$lead->name}. Ya tenemos la información necesaria. Nuestro equipo va a revisar el caso y te va a contactar por {$channel}."
                : "Gracias, {$lead->name}. Ya tenemos la información necesaria. Nuestro equipo va a revisar el caso y te va a contactar por {$channel}.";

            return $this->close($conversation, $lead, $message, 'close_sufficient', 'pending_review', $evidence);
        }

        $question = $this->questionSelector->next($playbook, $lead);
        PacoTrace::debug('next_turn.decision', [
            'conversation_id' => $conversation->id,
            'stage' => $conversation->stage->value,
            'fit' => $fit->value,
            'sufficiency' => $sufficiency['score'] ?? null,
            'has_contact' => $hasContact,
            'next_question' => $question?->code,
        ]);

        if (! $question) {
            if ($hasContact) {
                $channel = $lead->contact_channel === 'whatsapp' ? 'WhatsApp' : 'email';
                $trustMessage = $evidence ? $evidence['message'].' ' : '';

                return $this->close(
                    $conversation,
                    $lead,
                    $trustMessage."Gracias, {$lead->name}. Ya tenemos la información necesaria. Nuestro equipo te va a contactar por {$channel}.",
                    'close_sufficient',
                    'pending_review',
                    $evidence,
                );
            }

            return $this->turns->contact();
        }

        $state['asked_questions'][] = $question->code;
        $lead->state = $state;
        $lead->save();
        $conversation->stage = $evidence
            ? ConversationStage::TrustBuilding
            : ($hasContact ? ConversationStage::Qualifying : ConversationStage::UnderstandingNeed);
        $conversation->save();

        $prefix = match (true) {
            $evidence !== null => $evidence['message'],
            $evidenceAttemptedNow => "Gracias, {$lead->name}. Para entender mejor tu caso:",
            $conversation->useful_interaction_count === 1 => $state['initial_acknowledgement'] ?? 'Entendimos.',
            default => null,
        };

        return $this->turns->question($question, $prefix, $evidence);
    }

    private function reachedInteractionLimit(Conversation $conversation): bool
    {
        $maxInteractions = $conversation->campaign?->max_interactions
            ?? $conversation->playbook?->max_interactions
            ?? 7;

        return $conversation->useful_interaction_count >= $maxInteractions
            || $conversation->interaction_count >= ($maxInteractions + 5);
    }

    /** @return array<string, mixed> */
    private function commercialEvidenceContext(Conversation $conversation, Lead $lead): array
    {
        $turns = $conversation->events()
            ->orderBy('sequence')
            ->get(['sequence', 'actor', 'payload'])
            ->map(function (ConversationEvent $event): ?array {
                $text = $event->actor === 'assistant'
                    ? data_get($event->payload, 'turn.message')
                    : data_get($event->payload, 'display');
                if (! is_string($text) || blank($text) || $text === 'Datos de contacto compartidos') {
                    return null;
                }

                return [
                    'sequence' => $event->sequence,
                    'speaker' => $event->actor === 'assistant' ? 'paco' : 'visitor',
                    'message' => Str::limit(Str::squish($text), 600, '…'),
                ];
            })
            ->filter()
            ->take(-16)
            ->values()
            ->all();

        return [
            'conversation' => $turns,
            'stage' => $conversation->stage->value,
            'answered_fields' => collect(($lead->state ?? [])['answered_fields'] ?? [])->except('contact')->all(),
            'question_priorities' => ($lead->state ?? [])['question_priority_codes'] ?? [],
        ];
    }

    /** @return array<string, mixed> */
    private function close(
        Conversation $conversation,
        Lead $lead,
        string $message,
        string $objective,
        string $leadStatus,
        ?array $evidence = null,
    ): array {
        $score = $this->score($lead);
        $conversation->forceFill([
            'status' => ConversationStatus::Closed,
            'stage' => $leadStatus === 'pending_review'
                ? ConversationStage::ClosedPendingReview
                : ConversationStage::ClosedAbandoned,
            'closed_at' => now(),
        ])->save();

        $lead->forceFill([
            'status' => $leadStatus,
            'score' => $score,
            'score_confidence' => 0.750,
            'next_action' => $leadStatus === 'pending_review' ? 'manual_review' : 'none',
            'summary' => $lead->problem_summary,
            'qualified_at' => $leadStatus === 'pending_review' ? now() : null,
        ])->save();

        $lead->scores()->create([
            'score_total' => $score,
            'fit_score' => $lead->fit_level === 'supported' ? 25 : 10,
            'clarity_score' => filled($lead->problem_summary) ? 15 : 0,
            'decision_score' => filled($lead->decision_role) ? 15 : 0,
            'readiness_score' => $leadStatus === 'pending_review' ? 15 : 0,
            'interaction_score' => 5,
            'rules_version' => 'deterministic-v1',
            'explanation' => ['mode' => 'bootstrap'],
        ]);

        SendLeadNotification::dispatch($lead->id);

        return $this->turns->closing($message, $objective, $evidence);
    }

    /** @param array<string, mixed> $turn */
    private function recordContentImpressions(
        Conversation $conversation,
        ConversationEvent $event,
        array $turn,
    ): void {
        $carousel = collect($turn['parts'] ?? [])->firstWhere('type', 'content_carousel');
        if (! is_array($carousel)) {
            return;
        }

        foreach (($carousel['items'] ?? []) as $rank => $item) {
            if (! is_array($item) || ! isset($item['entity_type'], $item['entity_id'])) {
                continue;
            }

            ContentImpression::query()->create([
                'conversation_id' => $conversation->id,
                'event_id' => $event->id,
                'entity_type' => $item['entity_type'],
                'entity_id' => $item['entity_id'],
                'presentation_type' => 'compact',
                'rank' => $rank + 1,
                'reason_code' => $carousel['reason_code'] ?? null,
            ]);
        }
    }

    private function score(Lead $lead): int
    {
        $score = 0;
        $score += $lead->fit_level === 'supported' ? 25 : ($lead->fit_level === 'conditional' ? 15 : 5);
        $score += filled($lead->problem_summary) ? 15 : 0;
        $score += filled($lead->name) && filled($lead->contact_channel) ? 15 : 0;
        $score += filled($lead->decision_role) ? 15 : 0;
        $score += count(($lead->state ?? [])['answered_fields'] ?? []) >= 3 ? 15 : 5;

        return min(100, $score);
    }

    private function setAttribute(
        Lead $lead,
        string $field,
        mixed $value,
        ConversationEvent $event,
        string $evidenceType,
        float $confidence = 1,
        ?string $evidenceText = null,
    ): void {
        $lead->attributes()
            ->where('field_code', $field)
            ->where('is_current', true)
            ->update(['is_current' => false, 'superseded_at' => now()]);

        $lead->attributes()->create([
            'field_code' => $field,
            'value_json' => is_array($value) ? $value : null,
            'value_text' => is_scalar($value) ? (string) $value : null,
            'evidence_type' => $evidenceType,
            'evidence_text' => Str::limit($evidenceText ?? $this->scalarDisplay($value), 500),
            'confidence' => $confidence,
            'source_event_id' => $event->id,
            'is_current' => true,
            'surface_to_user' => $evidenceType === 'explicit',
        ]);
    }

    /** @param array<string, mixed> $action */
    private function displayValue(array $action): string
    {
        if (($action['type'] ?? null) === 'confirm_prefill') {
            return (string) ($action['values']['initial_query'] ?? 'Datos de campaña confirmados');
        }
        if (($action['type'] ?? null) === 'contact_submit') {
            return 'Datos de contacto compartidos';
        }

        $value = $action['value'] ?? '';
        $question = Question::query()->where('code', $action['component_id'] ?? '')->first();
        if ($question?->options) {
            $labels = collect($question->options)->keyBy('value');
            if ($question->component_type === 'single_select' && is_array($value)) {
                $choice = (string) ($value['choice'] ?? '');
                $label = (string) ($labels->get($choice)['label'] ?? $choice);
                $detail = Str::of((string) ($value['detail'] ?? ''))->squish()->toString();

                return $detail !== '' ? "{$label} — {$detail}" : $label;
            }
            if (is_array($value)) {
                return collect($value)->map(fn ($item): string => (string) ($labels->get($item)['label'] ?? $item))->join(', ');
            }

            return (string) ($labels->get($value)['label'] ?? $value);
        }

        return $this->scalarDisplay($value);
    }

    private function normalizeQuestionValue(Question $question, mixed $value): mixed
    {
        if ($question->component_type !== 'single_select' || ! $question->options) {
            return $value;
        }

        $choice = is_array($value) ? (string) ($value['choice'] ?? '') : (string) $value;
        $option = collect($question->options)->firstWhere('value', $choice);
        if (! is_array($option)) {
            throw ValidationException::withMessages(['action.value' => 'Elegí una opción válida.']);
        }

        if (! is_array($value) || ! ($option['allow_detail'] ?? false)) {
            return $choice;
        }

        $maxLength = max(3, min(1500, (int) ($option['detail_max_length'] ?? 600)));
        $detail = Str::of((string) ($value['detail'] ?? ''))
            ->squish()
            ->limit($maxLength, '')
            ->toString();

        if (($option['detail_required'] ?? false) && mb_strlen($detail) < 3) {
            throw ValidationException::withMessages(['action.value.detail' => 'Contanos brevemente un poco más.']);
        }

        return ['choice' => $choice, 'detail' => $detail !== '' ? $detail : null];
    }

    private function scalarDisplay(mixed $value): string
    {
        if (is_array($value)) {
            return collect($value)->map(fn ($item): string => is_scalar($item) ? (string) $item : '')->filter()->join(', ');
        }

        return is_scalar($value) ? (string) $value : '';
    }

    private function appendEvent(
        Conversation $conversation,
        string $actor,
        string $kind,
        array $payload,
        ?string $idempotencyKey = null,
    ): ConversationEvent {
        $sequence = ((int) $conversation->events()->max('sequence')) + 1;

        return $conversation->events()->create([
            'sequence' => $sequence,
            'actor' => $actor,
            'kind' => $kind,
            'payload' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed>|null */
    private function consumePrefill(mixed $token, Campaign $campaign): ?array
    {
        if (! is_string($token) || ! preg_match('/^[A-Za-z0-9_-]{24,120}$/', $token)) {
            return null;
        }

        $payload = Cache::pull("paco:prefill:{$token}");
        if (! is_array($payload) || (($payload['campaign'] ?? $campaign->code) !== $campaign->code)) {
            return null;
        }

        $prefill = Arr::only($payload, ['name', 'email', 'phone', 'contact_channel', 'initial_query']);
        $prefill['requires_confirmation'] = true;

        return $prefill;
    }

    private function assertOriginAllowed(Campaign $campaign, mixed $originHost): void
    {
        if (! is_string($originHost) || $originHost === '' || empty($campaign->allowed_origins)) {
            return;
        }

        $host = Str::lower($originHost);
        $allowed = collect($campaign->allowed_origins)->contains(function (mixed $candidate) use ($host): bool {
            if (! is_string($candidate) || $candidate === '') {
                return false;
            }

            $candidate = Str::lower($candidate);

            return $candidate === $host
                || (str_starts_with($candidate, '*.') && str_ends_with($host, substr($candidate, 1)));
        });

        if (! $allowed) {
            throw ValidationException::withMessages([
                'campaign' => 'Esta campaña no está habilitada para el origen solicitado.',
            ]);
        }
    }
}
