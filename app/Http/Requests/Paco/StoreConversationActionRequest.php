<?php

declare(strict_types=1);

namespace App\Http\Requests\Paco;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreConversationActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'conversation_version' => ['required', 'integer', 'min:1'],
            'action' => ['required', 'array'],
            'action.type' => ['required', Rule::in([
                'text_submit', 'single_select', 'multi_select', 'date_submit',
                'contact_submit', 'confirm_prefill',
            ])],
            'action.component_id' => ['required_unless:action.type,confirm_prefill', 'string', 'max:100'],
            'action.value' => ['required_unless:action.type,confirm_prefill'],
            'action.values' => ['required_if:action.type,confirm_prefill', 'array'],
            'action.values.name' => ['nullable', 'string', 'max:255'],
            'action.values.contact_channel' => ['nullable', Rule::in(['email', 'whatsapp'])],
            'action.values.channel' => ['nullable', Rule::in(['email', 'whatsapp'])],
            'action.values.contact_value' => ['nullable', 'string', 'max:255'],
            'action.values.initial_query' => ['required_if:action.type,confirm_prefill', 'string', 'min:3', 'max:'.config('paco.max_message_length')],
            'turn_context' => ['nullable', 'array'],
            'turn_context.visible_content_ids' => ['nullable', 'array', 'max:20'],
        ];
    }
}
