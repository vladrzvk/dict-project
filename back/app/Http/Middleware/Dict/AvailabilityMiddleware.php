<?php

namespace App\Http\Middleware\Dict;

use Closure;
use Illuminate\Support\Facades\Log;

class AvailabilityMiddleware
{
    public function handle($request, Closure $next)
    {
        // Mesurer le temps de démarrage
        $startTime = microtime(true);
        
        // Vérifier l'état du système
        if (!$this->checkSystemHealth()) {
            Log::critical('DICT:Disponibilité - Le système est indisponible');
            return response()->json(['error' => 'Service temporairement indisponible'], 503);
        }
        
        $response = $next($request);
        
        // Mesurer le temps de réponse
        $responseTime = microtime(true) - $startTime;
        Log::info('DICT:Disponibilité - Temps de réponse', ['duration_ms' => $responseTime * 1000]);
        
        return $response;
    }
    
    private function checkSystemHealth()
    {
        try {
            // Vérifier l'accès à la base de données
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('DICT:Disponibilité - Erreur de connexion DB: ' . $e->getMessage());
            return false;
        }
    }
}