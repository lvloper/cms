<?php

namespace App\Http\Middleware;

use App\Models\Redirection;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RedirectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar en peticiones GET
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Obtener la URL actual sin query parameters y normalizar igual que el modelo
        $currentPath = \App\Models\Redirection::normalizePath($request->getPathInfo());

        // Buscar redirección en caché primero para mejor performance
        $redirection = Cache::remember(
            'redirection:' . md5($currentPath),
            now()->addMinutes(60), // Cache por 1 hora
            function () use ($currentPath) {
                return Redirection::where('old_url', $currentPath)
                    ->where('is_active', true)
                    ->whereNotNull('new_url') // Solo redirecciones con destino
                    ->first();
            }
        );

        if ($redirection) {
            // Determinar la URL de destino
            $newUrl = $redirection->new_url;
            
            // Si no es una URL externa, convertir a URL completa
            if (!$redirection->is_external) {
                // Asegurar que empiece con /
                if (!str_starts_with($newUrl, '/')) {
                    $newUrl = '/' . $newUrl;
                }
                $newUrl = url($newUrl);
            }

            // Preservar query parameters si existen
            if ($request->getQueryString()) {
                $newUrl .= '?' . $request->getQueryString();
            }

            // Realizar la redirección con el código apropiado
            return redirect($newUrl, $redirection->redirect_code);
        }

        // Continue with normal request
        $response = $next($request);

        return $response;
    }
}