<?php

namespace App\Http\Middleware\Dict;

use Closure;
use Illuminate\Support\Facades\Log;
use App\Http\Services\Dict\DictIntegrityService;

class IntegrityMiddleware
{
    /**
     * @var DictIntegrityService
     */
    protected $integrityService;
    
    /**
     * Constructeur avec injection de dépendance
     */
    public function __construct(DictIntegrityService $integrityService)
    {
        $this->integrityService = $integrityService;
    }
    
    public function handle($request, Closure $next)
    {
        // Validation des données d'entrée pour les requêtes POST/PUT
        if ($request->isMethod('post') || $request->isMethod('put')) {
            $rules = $this->getValidationRules($request);
            
            if (!empty($rules)) {
                $validationResult = $this->integrityService->validateData($request->all(), $rules);
                
                if (!$validationResult['valid']) {
                    Log::warning('DICT:Intégrité - Données invalides', ['errors' => $validationResult['errors']]);
                    return response()->json(['error' => 'Données invalides', 'details' => $validationResult['errors']], 422);
                }
            }
        }
        
        $response = $next($request);
        
        return $response;
    }
    
    /**
     * Obtenir les règles de validation en fonction de la route
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function getValidationRules($request)
    {
        $rules = [];
        $path = $request->path();
        
        // Définir des règles de validation spécifiques selon les routes
        if (strpos($path, 'api/articles') !== false) {
            $rules = [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'author' => 'nullable|string|max:100',
                'published' => 'boolean'
            ];
        }
        
        return $rules;
    }
}