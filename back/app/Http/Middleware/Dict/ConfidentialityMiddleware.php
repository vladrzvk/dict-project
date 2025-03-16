<?php

namespace App\Http\Middleware\Dict;

use Closure;
use Illuminate\Support\Facades\Log;
use App\Http\Services\Dict\DictConfidentialityService;

class ConfidentialityMiddleware
{
    /**
     * @var DictConfidentialityService
     */
    protected $confidentialityService;
    
    /**
     * Constructeur avec injection de dépendance
     */
    public function __construct(DictConfidentialityService $confidentialityService)
    {
        $this->confidentialityService = $confidentialityService;
        
        // Configurer le service en fonction de l'environnement
        if (app()->environment('production')) {
            $this->confidentialityService->configureForProduction();
        } else {
            $this->confidentialityService->configureForDevelopment();
        }
    }
    
    public function handle($request, Closure $next)
    {
        // Vérifier si la requête contient des données sensibles
        if ($this->confidentialityService->containsSensitiveData($request)) {
            // Journaliser sans bloquer la requête
            Log::warning('DICT:Confidentialité - Requête avec données sensibles', [
                'ip' => $request->ip(),
                'method' => $request->method(),
                'path' => $request->path()
            ]);
        }
        
        $response = $next($request);
        
        // Appliquer les en-têtes de sécurité à la réponse
        return $this->confidentialityService->applySecurityHeaders($response);
    }
}