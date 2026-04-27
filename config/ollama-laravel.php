<?php

// Config for Cloudstudio/Ollama

return [
    'model' => env('OLLAMA_MODEL', 'llama3'),
    'filament-magic-model' => env('OLLAMA_FILAMENT_MODEL', 'codestral'),
    'url' => env('OLLAMA_URL', 'http://localhost:11437'),
    'default_prompt' => env('OLLAMA_DEFAULT_PROMPT', 'Hello, how can I assist you today?'),
    'connection' => [
        'timeout' => env('OLLAMA_CONNECTION_TIMEOUT', 300),
    ],
];
