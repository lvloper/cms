<?php

declare(strict_types=1);

namespace App\Support\Paco;

use Illuminate\Support\Facades\Log;

final class PacoTrace
{
    /** @param array<string, mixed> $context */
    public static function debug(string $event, array $context = []): void
    {
        if (! app()->environment(['local', 'development', 'testing'])) {
            return;
        }

        Log::channel('paco')->debug($event, self::sanitize($context));
    }

    /** @param array<string, mixed> $context */
    private static function sanitize(array $context): array
    {
        foreach (['email', 'phone', 'contact_value', 'client_ip', 'user_agent'] as $key) {
            if (array_key_exists($key, $context)) {
                $context[$key] = '[redacted]';
            }
        }

        return $context;
    }
}
