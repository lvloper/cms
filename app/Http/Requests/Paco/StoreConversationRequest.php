<?php

declare(strict_types=1);

namespace App\Http\Requests\Paco;

use Illuminate\Foundation\Http\FormRequest;

final class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'campaign' => ['sometimes', 'string', 'max:100'],
            'prefill_token' => ['nullable', 'string', 'max:120'],
            'origin_url' => ['nullable', 'url', 'max:2048'],
            'referrer' => ['nullable', 'url', 'max:2048'],
            'locale' => ['sometimes', 'string', 'max:12'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'page_context' => ['nullable', 'array'],
            'page_context.content_type' => ['nullable', 'string', 'max:100'],
            'page_context.content_id' => ['nullable', 'integer'],
        ];
    }
}
