<?php

declare(strict_types=1);

return [
    'model_driver' => env('PACO_MODEL_DRIVER', 'opencode_go'),
    'default_campaign' => env('PACO_DEFAULT_CAMPAIGN', 'home_default'),
    'direct_campaign' => env('PACO_DIRECT_CAMPAIGN', 'direct_default'),
    'client_closing_message' => env(
        'PACO_CLIENT_CLOSING_MESSAGE',
        'Hola, ¿te gustaría hacer algo similar para tu organización? Contanos tu caso.',
    ),
    'max_message_length' => (int) env('PACO_MAX_MESSAGE_LENGTH', 1500),
    'min_intent_confidence' => (float) env('PACO_MIN_INTENT_CONFIDENCE', 0.45),
    'prefill_ttl_minutes' => (int) env('PACO_PREFILL_TTL_MINUTES', 60),
    'ip_hash_salt' => env('PACO_IP_HASH_SALT', env('APP_KEY')),
    'lead_notification_to' => env('PACO_LEAD_NOTIFICATION_TO', env('MAIL_FROM_ADDRESS')),
    'opencode_go' => [
        'api_key' => env('OPENCODE_API_KEY'),
        'base_url' => env('OPENCODE_BASE_URL', 'https://opencode.ai/zen/go/v1'),
        'model' => env('OPENCODE_MODEL', 'mimo-v2.5'),
        'max_tokens' => (int) env('OPENCODE_MAX_TOKENS', 1600),
        'timeout_seconds' => (int) env('OPENCODE_TIMEOUT_SECONDS', 30),
        'connect_timeout_seconds' => (int) env('OPENCODE_CONNECT_TIMEOUT_SECONDS', 10),
    ],
    'fallback' => [
        'enabled' => filter_var(env('PACO_AI_FALLBACK_ENABLED', true), FILTER_VALIDATE_BOOL),
        'cooldown_minutes' => (int) env('PACO_AI_FALLBACK_COOLDOWN_MINUTES', 10),
    ],
    'rate_limits' => [
        'enabled' => filter_var(env('PACO_RATE_LIMIT_ENABLED', env('APP_ENV') === 'production'), FILTER_VALIDATE_BOOL),
        'create' => env('PACO_CREATE_RATE_LIMIT', '5,10'),
        'show' => env('PACO_SHOW_RATE_LIMIT', '30,10'),
        'action' => env('PACO_ACTION_RATE_LIMIT', '20,10'),
    ],
];
