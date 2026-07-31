<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Contracts\Paco\PacoModelGateway;
use App\Data\Paco\AnalysisResult;
use App\Data\Paco\EvidencePlan;
use App\Data\Paco\TurnInterpretation;
use Illuminate\Support\Str;

final class DeterministicPacoModelGateway implements PacoModelGateway
{
    /** @var array<string, array<int, string>> */
    private array $patterns = [
        'job' => ['busco trabajo', 'busqueda laboral', 'empleo', 'curriculum', 'currículum', 'enviar cv'],
        'vendor' => ['soy proveedor', 'somos proveedores', 'ofrecemos servicios', 'quiero ofrecerles'],
        'landing_page' => ['landing', 'pagina de campaña', 'página de campaña', 'micrositio'],
        'web_institucional' => [
            'sitio web', 'sitio institucional', 'pagina web', 'página web', 'una web', 'quiero una web',
            'web institucional', 'web para', 'web de',
            'sitio para', 'sitio de', 'barberia', 'barbería', 'restaurante', 'comercio local',
            'negocio local', 'negocio', 'local comercial', 'hacer una pagina', 'hacer una página',
            'crear una pagina', 'crear una página', 'pagina que', 'página que', 'chat de ayuda',
        ],
        'automatizacion' => ['automatizar', 'automatizacion', 'automatización', 'proceso manual', 'tarea repetitiva'],
        'integracion' => ['integrar', 'integracion', 'integración', 'conectar sistemas', 'api'],
        'mantenimiento' => ['mantenimiento', 'mantener', 'arreglar plataforma', 'evolucionar plataforma'],
        'servicio_mensual' => ['servicio mensual', 'bolsa de horas', 'capacidad tecnica', 'capacidad técnica'],
        'partnership' => ['somos una agencia', 'socio comercial', 'partner', 'subcontratar'],
        'plataforma_a_medida' => [
            'sistema a medida', 'sistema de administracion', 'sistema de administración',
            'sistema de gestion', 'sistema de gestión', 'plataforma', 'software a medida',
            'aplicacion', 'aplicación', 'ecommerce', 'e-commerce', 'tienda online',
        ],
        'consultoria' => ['consultoria', 'consultoría', 'asesoramiento', 'auditoria tecnica', 'auditoría técnica'],
        'support_existing_client' => ['ya somos clientes', 'soy cliente', 'soporte de nuestro'],
        'pack' => ['pack', 'paquete publicado'],
    ];

    /** @param array<string, mixed> $context */
    public function analyze(string $message, ?string $campaignIntent = null, array $context = []): AnalysisResult
    {
        $normalized = Str::of(Str::ascii($message))->lower()->squish()->toString();

        if ($this->refersToPreviousMessage($normalized) && filled($context['previous_need'] ?? null)) {
            return $this->analyze((string) $context['previous_need'], $campaignIntent);
        }

        foreach ($this->patterns as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($normalized, Str::ascii($pattern))) {
                    return new AnalysisResult(
                        primaryIntent: $intent,
                        confidence: 0.92,
                        facts: [[
                            'field' => 'primary_intent',
                            'value' => $intent,
                            'confidence' => 0.92,
                            'evidence' => $pattern,
                        ]],
                    );
                }
            }
        }

        return new AnalysisResult(
            primaryIntent: $campaignIntent ?: 'general',
            confidence: $campaignIntent ? 0.65 : 0.35,
        );
    }

    private function refersToPreviousMessage(string $message): bool
    {
        return collect([
            'ya te dije', 'te dije', 'ya lo explique', 'ya lo explique', 'como dije', 'lo mencione antes',
        ])->contains(fn (string $signal): bool => str_contains($message, $signal));
    }

    /** @param array<string, mixed> $context */
    public function interpretTurn(string $message, array $context): TurnInterpretation
    {
        $clean = Str::of($message)->squish()->toString();
        $normalized = Str::of(Str::ascii($clean))->lower()->toString();
        $alphanumeric = preg_replace('/[^a-z0-9]+/i', '', Str::ascii($clean)) ?? '';

        if (mb_strlen($alphanumeric) < 3) {
            return new TurnInterpretation(
                disposition: 'low_information',
                answersCurrentQuestion: false,
                useful: false,
                confidence: 0.99,
                reply: 'No llegamos a interpretar esa respuesta. Contanos un poco más.',
            );
        }

        foreach (['no me contestaste', 'no respondiste', 'no me respondiste', 'eso no responde'] as $signal) {
            if (str_contains($normalized, $signal)) {
                return new TurnInterpretation(
                    disposition: 'objection',
                    answersCurrentQuestion: false,
                    useful: false,
                    confidence: 0.96,
                    reply: 'Tenés razón: no respondimos lo que necesitabas. Aclaranos qué punto querés que retomemos.',
                );
            }
        }

        $questionStarters = ['que ', 'cual ', 'como ', 'cuando ', 'donde ', 'por que ', 'para que ', 'pueden ', 'podrian '];
        $isQuestion = str_contains($clean, '?')
            || collect($questionStarters)->contains(fn (string $starter): bool => str_starts_with($normalized, $starter))
            || str_contains($normalized, 'diferencia');

        if ($isQuestion) {
            return new TurnInterpretation(
                disposition: 'question',
                answersCurrentQuestion: false,
                useful: false,
                confidence: 0.9,
                reply: 'Es una consulta válida, pero nos falta contexto para responderla con precisión. Contanos qué opciones querés comparar.',
            );
        }

        return new TurnInterpretation(
            disposition: 'answer',
            answersCurrentQuestion: true,
            useful: true,
            confidence: 0.8,
            normalizedAnswer: $clean,
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array<string, mixed>>  $candidates
     */
    public function planEvidence(array $context, array $candidates): EvidencePlan
    {
        $works = collect($candidates)
            ->where('entity_type', 'work')
            ->unique('client_name')
            ->take(3);
        $selected = $works->values();

        if ($selected->isEmpty()) {
            $selected = collect($candidates)
                ->where('entity_type', 'testimonial')
                ->unique('client_name')
                ->take(3)
                ->values();
        }

        $testimonial = collect($candidates)
            ->where('entity_type', 'testimonial')
            ->sortByDesc(fn (array $candidate): int => $selected->pluck('client_name')->contains($candidate['client_name']) ? 1 : 0)
            ->first();

        if (is_array($testimonial) && ! $selected->contains('item_id', $testimonial['item_id'])) {
            $selected->push($testimonial);
        }

        return new EvidencePlan(
            selectedItemIds: $selected->take(4)->pluck('item_id')->all(),
            relationship: (string) ($selected->first()['reason_code'] ?? 'related_experience'),
            acknowledgement: filled($context['visitor_name'] ?? null) ? 'Sí, '.Str::ucfirst(Str::squish((string) $context['visitor_name'])).'.' : 'Sí.',
            testimonialItemId: is_array($testimonial) ? (string) $testimonial['item_id'] : null,
        );
    }

    public function provider(): string
    {
        return 'application';
    }

    public function model(): string
    {
        return 'deterministic-v1';
    }

    public function usedFallback(): bool
    {
        return false;
    }
}
