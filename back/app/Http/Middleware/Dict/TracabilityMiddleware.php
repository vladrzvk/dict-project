<?php

namespace App\Http\Middleware\Dict;

use Closure;
use Illuminate\Support\Facades\Log;
use App\Http\Services\Dict\DictTraceabilityService;

class TraceabilityMiddleware
{
    /**
     * @var DictTraceabilityService
     */
    protected $traceabilityService;
    
    /**
     * Constructeur avec injection de dépendance
     */
    public function __construct(DictTraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
    }
    
    public function handle($request, Closure $next)
    {
        // Marquer le début du traitement
        $startTime = microtime(true);
        
        // Générer un identifiant unique pour chaque requête
        $requestId = $request->header(config('dict.traceability.request_id_header'));
        
        if (!$requestId) {
            $requestId = $this->traceabilityService->generateRequestId();
            $request->headers->set(config('dict.traceability.request_id_header'), $requestId);
        }
        
        // Journaliser la requête
        $this->traceabilityService->logIncomingRequest($request, $requestId);
        
        $response = $next($request);
        
        // Ajouter l'identifiant à la réponse
        $response->headers->set(config('dict.traceability.request_id_header'), $requestId);
        
        // Journaliser la réponse
        $this->traceabilityService->logOutgoingResponse($response, $requestId, $startTime);
        
        return $response;
    }
}