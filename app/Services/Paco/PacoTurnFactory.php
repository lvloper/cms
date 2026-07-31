<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Models\Paco\Question;
use App\Models\Paco\ResponseBlock;

final class PacoTurnFactory
{
    /** @return array<string, mixed> */
    public function initial(string $message): array
    {
        return [
            'message' => $message,
            'parts' => [[
                'type' => 'text_input',
                'id' => 'initial_need',
                'required' => true,
                'multiline' => true,
                'max_length' => (int) config('paco.max_message_length'),
                'placeholder' => 'Contanos brevemente qué necesitás',
            ]],
            'meta' => ['objective' => 'clarify_need', 'allow_back' => false],
        ];
    }

    /** @return array<string, mixed> */
    public function clarifyNeed(): array
    {
        return $this->initial('Hola. ¿Cuál es el motivo de tu consulta? Contanos qué necesitás resolver o qué proyecto tenés en mente.');
    }

    /** @return array<string, mixed> */
    public function question(Question $question, ?string $prefix = null, ?array $evidence = null): array
    {
        $part = [
            'type' => $question->component_type,
            'id' => $question->code,
            'required' => ! $question->is_skippable,
            'allow_skip' => $question->is_skippable,
        ];

        if ($question->options) {
            $part['options'] = $question->options;
        }

        if ($question->component_type === 'text_input') {
            $part['multiline'] = true;
            $part['max_length'] = (int) ($question->validation_schema['max_length'] ?? config('paco.max_message_length'));
            $part['placeholder'] = 'Escribí una respuesta breve';
        }

        $parts = $evidence ? [[
            'type' => 'content_carousel',
            'id' => 'commercial_evidence',
            'items' => $evidence['items'],
            'reason_code' => $evidence['reason_code'],
        ]] : [];
        $parts[] = $part;

        return [
            'message' => trim(($prefix ? "{$prefix}\n\n" : '').$question->prompt),
            'parts' => $parts,
            'meta' => [
                'objective' => $question->field_code,
                'allow_back' => true,
                'question_text' => $question->prompt,
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function contact(): array
    {
        $message = ResponseBlock::query()->where('code', 'contact_transition_default')->value('text')
            ?? 'Ya tenemos un buen punto de partida. Para continuar y poder responderte, compartinos tus datos de contacto.';

        return [
            'message' => $message,
            'parts' => [[
                'type' => 'contact_form',
                'id' => 'contact',
                'required' => true,
                'fields' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Tu nombre', 'required' => true],
                    [
                        'name' => 'channel',
                        'type' => 'single_select',
                        'label' => '¿Cómo preferís que te contactemos?',
                        'required' => true,
                        'options' => [
                            ['value' => 'email', 'label' => 'Email'],
                            ['value' => 'whatsapp', 'label' => 'WhatsApp'],
                        ],
                    ],
                    ['name' => 'contact_value', 'type' => 'dynamic_contact', 'required' => true],
                ],
            ]],
            'meta' => ['objective' => 'collect_contact', 'allow_back' => true],
        ];
    }

    /** @return array<string, mixed> */
    public function closing(string $message, string $objective = 'close_sufficient', ?array $evidence = null): array
    {
        return [
            'message' => $message,
            'parts' => $evidence ? [[
                'type' => 'content_carousel',
                'id' => 'commercial_evidence',
                'items' => $evidence['items'],
                'reason_code' => $evidence['reason_code'],
            ]] : [],
            'meta' => ['objective' => $objective, 'allow_back' => false],
        ];
    }
}
