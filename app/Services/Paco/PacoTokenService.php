<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Models\Paco\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class PacoTokenService
{
    public function issue(): string
    {
        return Str::random(80);
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public function authorize(Request $request, Conversation $conversation): void
    {
        $token = $request->bearerToken();

        if (! is_string($token) || ! hash_equals($conversation->public_token_hash, $this->hash($token))) {
            throw new AccessDeniedHttpException('Token de conversación inválido.');
        }
    }
}
