<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Models\Paco\Conversation;
use App\Models\Paco\ConversationEvent;

final class PacoConversationPresenter
{
    /** @return array<string, mixed> */
    public function created(Conversation $conversation, string $token, ?array $prefill): array
    {
        $payload = $this->state($conversation);
        $payload['conversation_token'] = $token;
        $payload['prefill'] = $prefill;
        $payload['turn'] = $this->lastTurn($conversation);

        return $payload;
    }

    /** @return array<string, mixed> */
    public function action(Conversation $conversation): array
    {
        $payload = $this->state($conversation);
        $payload['turn'] = $this->lastTurn($conversation);

        return $payload;
    }

    /** @return array<string, mixed> */
    public function state(Conversation $conversation): array
    {
        $conversation->loadMissing(['events', 'campaign:id,code,name']);

        return [
            'conversation_id' => $conversation->id,
            'version' => $conversation->version,
            'status' => $conversation->status->value,
            'stage' => $conversation->stage->value,
            'campaign' => $conversation->campaign ? [
                'code' => $conversation->campaign->code,
                'name' => $conversation->campaign->name,
            ] : null,
            'turns' => $conversation->events
                ->filter(fn (ConversationEvent $event): bool => in_array($event->actor, ['user', 'assistant'], true))
                ->map(fn (ConversationEvent $event): array => $this->event($event))
                ->values()
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function event(ConversationEvent $event): array
    {
        if ($event->actor === 'assistant') {
            return [
                'id' => $event->id,
                'actor' => 'assistant',
                ...($event->payload['turn'] ?? []),
                'created_at' => $event->created_at?->toIso8601String(),
            ];
        }

        return [
            'id' => $event->id,
            'actor' => 'user',
            'message' => (string) ($event->payload['display'] ?? ''),
            'parts' => [],
            'meta' => ['revisable' => true],
            'created_at' => $event->created_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed>|null */
    private function lastTurn(Conversation $conversation): ?array
    {
        $event = $conversation->events->last(fn (ConversationEvent $item): bool => $item->actor === 'assistant');

        return $event ? ['id' => $event->id, ...($event->payload['turn'] ?? [])] : null;
    }
}
