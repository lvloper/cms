<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paco\StoreConversationActionRequest;
use App\Http\Requests\Paco\StoreConversationRequest;
use App\Models\Paco\Conversation;
use App\Services\Paco\PacoConversationPresenter;
use App\Services\Paco\PacoConversationService;
use App\Services\Paco\PacoTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PacoConversationController extends Controller
{
    public function __construct(
        private readonly PacoConversationService $conversations,
        private readonly PacoConversationPresenter $presenter,
        private readonly PacoTokenService $tokens,
    ) {}

    public function store(StoreConversationRequest $request): JsonResponse
    {
        $created = $this->conversations->create($request->validated(), [
            'origin_host' => $this->originHost($request),
            'ip_hash' => $this->ipHash($request),
            'country_code' => $this->countryCode($request),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);

        return response()->json(
            $this->presenter->created($created['conversation'], $created['token'], $created['prefill']),
            201,
        );
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->tokens->authorize($request, $conversation);

        return response()->json($this->presenter->state($conversation));
    }

    public function action(
        StoreConversationActionRequest $request,
        Conversation $conversation,
    ): JsonResponse {
        $this->tokens->authorize($request, $conversation);
        $idempotencyKey = (string) $request->header('Idempotency-Key');

        if (! Str::isUuid($idempotencyKey)) {
            throw ValidationException::withMessages([
                'Idempotency-Key' => 'El header Idempotency-Key debe contener un UUID.',
            ]);
        }

        $updated = $this->conversations->act(
            $conversation,
            $request->validated('action'),
            (int) $request->validated('conversation_version'),
            $idempotencyKey,
        );

        return response()->json($this->presenter->action($updated));
    }

    private function originHost(Request $request): ?string
    {
        $origin = $request->headers->get('Origin') ?: $request->input('origin_url');

        return is_string($origin) ? parse_url($origin, PHP_URL_HOST) : null;
    }

    private function ipHash(Request $request): ?string
    {
        $ip = $request->ip();

        return $ip ? hash_hmac('sha256', $ip, (string) config('paco.ip_hash_salt')) : null;
    }

    private function countryCode(Request $request): ?string
    {
        $country = Str::upper((string) $request->header('CF-IPCountry'));

        return preg_match('/^[A-Z]{2}$/', $country) ? $country : null;
    }
}
