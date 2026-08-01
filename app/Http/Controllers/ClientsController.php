<?php

namespace App\Http\Controllers;

use App\Services\ClientPreviewCollection;
use Inertia\Inertia;
use Inertia\Response;

class ClientsController extends Controller
{
    public function __invoke(ClientPreviewCollection $clientPreviews): Response
    {
        return Inertia::render('Clients', [
            'clients' => $clientPreviews->featured(),
        ]);
    }
}
