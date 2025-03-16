<?php

namespace App\Http\Middleware\Dict;

use Closure;
use Illuminate\Support\Facades\Log;

class ConfidentialityMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        // Ajouter des en-têtes de sécurité
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; connect-src 'self'");
        
        return $response;
    }
}