<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Models\Paco\Campaign;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PacoPrefillService
{
    /**
     * @param  array<string, mixed>  $values
     * @param  array<string, string|null>  $tracking
     */
    public function createLink(Campaign $campaign, array $values, array $tracking = []): string
    {
        if ($campaign->status !== 'active') {
            throw ValidationException::withMessages([
                'campaign' => 'La campaña debe estar activa antes de generar enlaces.',
            ]);
        }

        $payload = array_filter(Arr::only($values, [
            'name', 'email', 'phone', 'contact_channel', 'initial_query',
        ]), static fn (mixed $value): bool => filled($value));

        if ($payload === []) {
            throw ValidationException::withMessages([
                'prefill' => 'Ingresá al menos un dato para precargar.',
            ]);
        }

        Validator::make($payload, [
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'email' => ['sometimes', 'email:rfc', 'max:255'],
            'phone' => ['sometimes', 'regex:/^\\+?[0-9][0-9\\s().-]{7,24}$/'],
            'contact_channel' => ['sometimes', 'in:email,whatsapp'],
            'initial_query' => ['sometimes', 'string', 'min:3', 'max:'.config('paco.max_message_length')],
        ])->validate();

        $token = Str::random(48);
        Cache::put(
            "paco:prefill:{$token}",
            ['campaign' => $campaign->code, ...$payload],
            now()->addMinutes((int) config('paco.prefill_ttl_minutes')),
        );

        $query = array_filter([
            'campaign' => $campaign->code,
            'prefill_token' => $token,
            'utm_source' => $tracking['utm_source'] ?? null,
            'utm_medium' => $tracking['utm_medium'] ?? null,
            'utm_campaign' => $tracking['utm_campaign'] ?? null,
        ], static fn (mixed $value): bool => filled($value));

        return route('paco.show').'?'.http_build_query($query);
    }
}
