<?php

declare(strict_types=1);

use App\Http\Controllers\Api\PacoConversationController;
use Illuminate\Support\Facades\Route;

Route::prefix('paco')->name('api.paco.')->group(function (): void {
    $throttle = static fn (string $key): array => config('paco.rate_limits.enabled')
        ? ['throttle:'.config("paco.rate_limits.{$key}")]
        : [];

    Route::post('/conversations', [PacoConversationController::class, 'store'])
        ->middleware($throttle('create'))
        ->name('conversations.store');
    Route::get('/conversations/{conversation}', [PacoConversationController::class, 'show'])
        ->middleware($throttle('show'))
        ->name('conversations.show');
    Route::post('/conversations/{conversation}/actions', [PacoConversationController::class, 'action'])
        ->middleware($throttle('action'))
        ->name('conversations.action');
});
