<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Contracts\Paco\PacoModelGateway;
use App\Models\Client;
use App\Models\Paco\Lead;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class PacoEvidenceRetriever
{
    public function __construct(private readonly PacoModelGateway $model) {}

    /** @var array<string, array<int, string>> */
    private const INTENT_TERMS = [
        'landing_page' => ['landing', 'campaña', 'donaciones', 'conversión', 'marketing'],
        'web_institucional' => ['web', 'sitio', 'institucional', 'contenidos', 'cms'],
        'plataforma_a_medida' => ['plataforma', 'sistema', 'operación', 'procesos', 'administración'],
        'automatizacion' => ['automatización', 'proceso', 'integración', 'carga manual'],
        'integracion' => ['integración', 'api', 'sistemas', 'datos'],
        'mantenimiento' => ['mantenimiento', 'soporte', 'evolución', 'mejoras'],
        'servicio_mensual' => ['mensual', 'equipo', 'soporte', 'desarrollo'],
        'consultoria' => ['consultoría', 'estrategia', 'diagnóstico', 'producto'],
        'ecommerce' => ['ecommerce', 'tienda', 'catálogo', 'pagos', 'logística'],
    ];

    /**
     * @param  array<string, mixed>  $context
     * @return array{message: string, items: array<int, array<string, mixed>>, reason_code: string, composition: array<string, mixed>}|null
     */
    public function retrieve(Lead $lead, array $context = []): ?array
    {
        $knownContext = collect(($lead->state ?? [])['answered_fields'] ?? [])
            ->except('contact')
            ->map(fn (mixed $value): string => is_array($value)
                ? collect($value)->filter(fn (mixed $item): bool => is_scalar($item))->implode(' ')
                : (is_scalar($value) ? (string) $value : ''))
            ->filter()
            ->implode(' ');
        $query = $this->normalize(trim(
            ($lead->problem_summary ?? '').' '.($lead->primary_intent_code ?? '').' '.$knownContext,
        ));
        $intentTerms = self::INTENT_TERMS[$lead->primary_intent_code ?? ''] ?? [];
        $clients = Client::query()
            ->with('route')
            ->isPublished()
            ->where('paco_use_authorized', true)
            ->where('paco_chat_enabled', true)
            ->get();

        $works = collect();
        $testimonials = collect();

        foreach ($clients as $client) {
            $publicName = Str::squish((string) ($client->public_name ?: $client->title));
            if ($publicName === '') {
                continue;
            }

            $industryMatch = $this->matchesIndustry($query, (string) $client->industry);

            foreach (($client->works ?? collect())->values() as $index => $work) {
                if (! is_array($work) || ! ($work['use_authorized'] ?? false) || ! ($work['chat_enabled'] ?? false)) {
                    continue;
                }

                $workText = $this->normalize(collect([
                    $work['title'] ?? null,
                    $work['description'] ?? null,
                    $work['problem'] ?? null,
                    $work['solution'] ?? null,
                    $work['result'] ?? null,
                    $work['paco_text'] ?? null,
                    ...$this->list($work['categories'] ?? []),
                    ...$this->list($work['tags'] ?? []),
                ])->filter()->implode(' '));
                $problemMatch = $this->termMatches($workText, $intentTerms) + $this->overlap($query, $workText);
                if ($problemMatch === 0 && ! $industryMatch) {
                    continue;
                }

                $works->push([
                    'score' => 300 + ($problemMatch * 20) + ($industryMatch ? 100 : 0),
                    'reason_code' => $industryMatch && $problemMatch > 0
                        ? 'same_problem_same_industry'
                        : ($problemMatch > 0 ? 'same_problem' : 'same_industry'),
                    'client' => $client,
                    'client_name' => $publicName,
                    'index' => $index,
                    'work' => $work,
                ]);
            }

            foreach (($client->testimonials ?? collect())->values() as $index => $testimonial) {
                if (! is_array($testimonial) || ! ($testimonial['use_authorized'] ?? false) || ! ($testimonial['chat_enabled'] ?? false)) {
                    continue;
                }

                $quote = $this->plainText((string) ($testimonial['short_quote'] ?? $testimonial['testimonial'] ?? ''));
                if ($quote === '') {
                    continue;
                }

                $testimonialText = $this->normalize($quote.' '.($client->paco_summary ?? '').' '.($client->paco_chat_text ?? ''));
                $relevance = $this->termMatches($testimonialText, $intentTerms) + $this->overlap($query, $testimonialText);
                if ($relevance === 0 && ! $industryMatch) {
                    continue;
                }

                $testimonials->push([
                    'score' => 100 + ($relevance * 10) + ($industryMatch ? 100 : 0),
                    'reason_code' => $industryMatch ? 'same_industry' : 'testimonial_only',
                    'client' => $client,
                    'client_name' => $publicName,
                    'index' => $index,
                    'testimonial' => $testimonial,
                    'quote' => $quote,
                ]);
            }
        }

        $candidates = $works->sortByDesc('score')->take(8)
            ->map(fn (array $candidate): array => [
                ...$this->workItem($candidate),
                'score' => $candidate['score'],
                'reason_code' => $candidate['reason_code'],
            ])
            ->concat($testimonials->sortByDesc('score')->take(8)->map(fn (array $candidate): array => [
                ...$this->testimonialItem($candidate),
                'score' => $candidate['score'],
                'reason_code' => $candidate['reason_code'],
            ]))
            ->values()
            ->all();
        if ($candidates === []) {
            return null;
        }

        $plan = $this->model->planEvidence([
            ...$context,
            'visitor_name' => $lead->name,
            'primary_intent' => $lead->primary_intent_code,
            'problem_summary' => $lead->problem_summary,
            'known_facts' => collect(($lead->state ?? [])['answered_fields'] ?? [])->except('contact')->all(),
        ], $candidates);
        $selected = collect($candidates)
            ->whereIn('item_id', $plan->selectedItemIds)
            ->sortBy(fn (array $item): int => array_search($item['item_id'], $plan->selectedItemIds, true))
            ->values();
        if ($selected->isEmpty()) {
            return null;
        }

        $items = $selected->map(fn (array $item): array => collect($item)->except(['score', 'reason_code'])->all())->all();

        return [
            'message' => $this->composeMessage($lead, $items, $plan->acknowledgement, $plan->testimonialItemId),
            'items' => $items,
            'reason_code' => $plan->relationship,
            'composition' => [
                ...$plan->toArray(),
                'provider' => $this->model->provider(),
                'model' => $this->model->model(),
                'used_fallback' => $this->model->usedFallback(),
                'candidate_count' => count($candidates),
            ],
        ];
    }

    /** @param array<string, mixed> $candidate
     * @return array<string, mixed>
     */
    private function workItem(array $candidate): array
    {
        /** @var Client $client */
        $client = $candidate['client'];
        $work = $candidate['work'];
        $approvedText = $this->plainText((string) ($work['paco_text'] ?? $work['solution'] ?? $work['description'] ?? ''));

        return [
            'item_id' => "client-{$client->id}-work-{$candidate['index']}",
            'entity_type' => 'work',
            'entity_id' => $client->id,
            'client_name' => $candidate['client_name'],
            'title' => Str::squish((string) ($work['title'] ?? 'Proyecto relacionado')),
            'problem' => $this->plainText((string) ($work['problem'] ?? '')),
            'solution' => $this->plainText((string) ($work['solution'] ?? $approvedText)),
            'result' => $this->plainText((string) ($work['result'] ?? '')),
            'url' => $this->safeUrl((string) ($work['external_url'] ?? $client->url)),
            'image_url' => $this->publicAssetUrl($work['image'] ?? null),
            'client_logo_url' => $this->publicAssetUrl($client->logo),
        ];
    }

    /** @param array<string, mixed> $candidate
     * @return array<string, mixed>
     */
    private function testimonialItem(array $candidate): array
    {
        /** @var Client $client */
        $client = $candidate['client'];
        $testimonial = $candidate['testimonial'];

        return [
            'item_id' => "client-{$client->id}-testimonial-{$candidate['index']}",
            'entity_type' => 'testimonial',
            'entity_id' => $client->id,
            'client_name' => $candidate['client_name'],
            'quote' => $this->shortExcerpt((string) $candidate['quote']),
            'author' => Str::squish((string) ($testimonial['person'] ?? '')),
            'role' => Str::squish((string) ($testimonial['position'] ?? '')),
            'url' => $this->safeUrl((string) ($testimonial['source_url'] ?? $client->url)),
            'image_url' => $this->publicAssetUrl($testimonial['image'] ?? null),
            'client_logo_url' => $this->publicAssetUrl($client->logo),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function composeMessage(Lead $lead, array $items, ?string $acknowledgement, ?string $testimonialItemId): string
    {
        $opening = filled($acknowledgement)
            ? Str::finish(Str::squish((string) $acknowledgement), '.')
            : (filled($lead->name) ? 'Sí, '.Str::ucfirst((string) $lead->name).'.' : 'Sí.');
        $works = collect($items)->where('entity_type', 'work');
        $clientNames = collect($items)->pluck('client_name')->filter()->unique()->values();

        if ($works->count() > 1) {
            $message = $opening.' Hemos realizado proyectos de estas características para '.
                $this->humanList($works->pluck('client_name')->filter()->unique()->values()->all()).'.';
        } elseif ($works->count() === 1) {
            $work = $works->first();
            $message = $opening." Con {$work['client_name']} realizamos {$work['title']}.";
            if (filled($work['solution'] ?? null)) {
                $message .= ' '.Str::finish((string) $work['solution'], '.');
            }
            if (filled($work['result'] ?? null)) {
                $message .= ' Como resultado documentado, '.Str::lcfirst(Str::finish((string) $work['result'], '.'));
            }
        } else {
            $message = $opening.' Trabajamos en proyectos digitales con organizaciones como '.
                $this->humanList($clientNames->all()).'. Estas experiencias pueden darte una referencia concreta de cómo acompañamos desafíos similares.';
        }

        $testimonial = collect($items)->firstWhere('item_id', $testimonialItemId);
        if (is_array($testimonial) && filled($testimonial['quote'] ?? null)) {
            $attributionParts = collect([$testimonial['author'] ?? null, $testimonial['role'] ?? null])
                ->filter()
                ->values();
            $attribution = $attributionParts->implode(', ');
            if (! str_contains($this->normalize($attribution), $this->normalize((string) $testimonial['client_name']))) {
                $attribution .= ($attribution !== '' ? ' de ' : '').$testimonial['client_name'];
            }
            $message .= "\n\nAdemás, ".($attribution !== '' ? $attribution : $testimonial['client_name']).
                ', señaló: “'.Str::finish($this->shortExcerpt((string) $testimonial['quote']), '.').'” Te invitamos a ver el testimonio completo.';
        }

        return $message;
    }

    /** @param array<int, string> $values */
    private function humanList(array $values): string
    {
        $values = array_values(array_filter(array_map(fn (string $value): string => '“'.Str::squish($value).'”', $values)));
        if (count($values) <= 1) {
            return $values[0] ?? 'otras organizaciones';
        }

        $last = array_pop($values);

        return implode(', ', $values).' y '.$last;
    }

    private function shortExcerpt(string $quote): string
    {
        $quote = preg_replace('/^[“”"\'\s]+|[“”"\'\s]+$/u', '', $this->plainText($quote)) ?? '';
        $sentences = preg_split('/(?<=[.!?])\s+/u', $quote, 2, PREG_SPLIT_NO_EMPTY);

        return Str::limit(trim($sentences[0] ?? $quote, " \t\n\r\0\x0B“”\"'"), 180, '…');
    }

    private function matchesIndustry(string $query, string $industry): bool
    {
        $industry = $this->normalize($industry);

        $aliases = [
            ['ong', 'fundacion', 'organizacion social', 'organizaciones sociales', 'derechos humanos'],
            ['ciencia', 'cientifico', 'investigacion', 'academico'],
            ['finanzas', 'financiero', 'banco', 'inversion'],
            ['salud', 'sanitario', 'clinica', 'hospital'],
            ['comercio', 'ecommerce', 'tienda', 'retail'],
        ];

        foreach ($aliases as $group) {
            $queryMatches = collect($group)->contains(fn (string $term): bool => str_contains($query, $term));
            $industryMatches = collect($group)->contains(fn (string $term): bool => str_contains($industry, $term));
            if ($queryMatches && $industryMatches) {
                return true;
            }
        }

        return $industry !== '' && collect(explode(' ', $industry))
            ->filter(fn (string $term): bool => mb_strlen($term) >= 4)
            ->contains(fn (string $term): bool => str_contains($query, $term));
    }

    /** @param array<int, string> $terms */
    private function termMatches(string $text, array $terms): int
    {
        return collect($terms)
            ->map(fn (string $term): string => $this->normalize($term))
            ->filter(fn (string $term): bool => $term !== '' && str_contains($text, $term))
            ->count();
    }

    private function overlap(string $query, string $text): int
    {
        $stopWords = ['para', 'como', 'con', 'una', 'uno', 'unos', 'unas', 'que', 'del', 'las', 'los', 'necesito', 'necesitamos', 'queremos'];
        $terms = collect(explode(' ', $query))
            ->filter(fn (string $term): bool => mb_strlen($term) >= 4 && ! in_array($term, $stopWords, true))
            ->unique();

        return $terms->filter(fn (string $term): bool => str_contains($text, $term))->count();
    }

    /** @return array<int, string> */
    private function list(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_map('strval', $value));
        }

        return is_string($value) ? preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] : [];
    }

    private function plainText(string $value): string
    {
        return Str::squish(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function normalize(string $value): string
    {
        return Str::of($this->plainText($value))->lower()->ascii()->toString();
    }

    private function safeUrl(string $url): ?string
    {
        return filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)
            ? $url
            : null;
    }

    private function publicAssetUrl(mixed $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }
        if ($this->safeUrl($path)) {
            return $path;
        }

        return Storage::disk('public')->url(ltrim($path, '/'));
    }
}
