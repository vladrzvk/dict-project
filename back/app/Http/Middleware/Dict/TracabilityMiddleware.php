<?php

namespace App\Http\Middleware\Dict;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TraceabilityMiddleware
{
    public function handle($request, Closure $next)
    {
        // Générer un identifiant unique pour chaque requête
        $requestId = (string) Str::uuid();
        $request->headers->set('X-Request-ID', $requestId);
        
        // Journaliser la requête
        Log::channel('dict')->info('DICT:Traçabilité - Requête reçue', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);
        
        $response = $next($request);
        
        // Ajouter l'identifiant à la réponse
        $response->headers->set('X-Request-ID', $requestId);
        
        // Journaliser la réponse
        Log::channel('dict')->info('DICT:Traçabilité - Réponse envoyée', [
            'request_id' => $requestId,
            'status' => $response->getStatusCode(),
            'duration_ms' => microtime(true) - LARAVEL_START
        ]);
        
        return $response;
    }
}