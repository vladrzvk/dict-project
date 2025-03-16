<?php

namespace App\Http\Middleware\Dict;

use Closure;
use Illuminate\Support\Facades\Log;
use App\Http\Services\Dict\DictAvailabilityService;

class AvailabilityMiddleware
{
    /**
     * @var DictAvailabilityService
     */
    protected $availabilityService;
    
    /**
     * Constructeur avec injection de dépendance
     */
    public function __construct(DictAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }
    
    public function handle($request, Closure $next)
    {
        // Mesurer le temps de démarrage
        $startTime = microtime(true);
        
        // Vérifier l'état du système
        $systemHealth = $this->availabilityService->checkSystemHealth();
        
        if ($systemHealth['status'] !== 'healthy') {
            Log::critical('DICT:Disponibilité - Le système est dégradé ou indisponible', $systemHealth);
            return response()->json(['error' => 'Service temporairement indisponible'], 503);
        }
        
        $response = $next($request);
        
        // Mesurer le temps de réponse
        $responseTime = microtime(true) - $startTime;
        Log::info('DICT:Disponibilité - Temps de réponse', ['duration_ms' => $responseTime * 1000]);
        
        return $response;
    }
}