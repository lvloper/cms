<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PacoPageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Paco/Show', [
            'entry' => [
                'campaign' => $request->string('campaign')->toString() ?: config('paco.direct_campaign'),
                'prefillToken' => $request->string('prefill_token')->toString() ?: null,
                'utmSource' => $request->string('utm_source')->toString() ?: null,
                'utmMedium' => $request->string('utm_medium')->toString() ?: null,
                'utmCampaign' => $request->string('utm_campaign')->toString() ?: null,
            ],
        ]);
    }
}
