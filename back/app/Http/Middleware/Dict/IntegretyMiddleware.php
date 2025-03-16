<?php

namespace App\Http\Middleware\Dict;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IntegrityMiddleware
{
    public function handle($request, Closure $next)
    {
        // Validation des données d'entrée pour les requêtes POST/PUT
        if ($request->isMethod('post') || $request->isMethod('put')) {
            $validationResult = $this->validateInput($request);
            
            if (!$validationResult['valid']) {
                Log::warning('DICT:Intégrité - Données invalides', ['errors' => $validationResult['errors']]);
                return response()->json(['error' => 'Données invalides', 'details' => $validationResult['errors']], 422);
            }
        }
        
        $response = $next($request);
        
        return $response;
    }
    
    private function validateInput($request)
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
        
        if (empty($rules)) {
            return ['valid' => true];
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray()
            ];
        }
        
        return ['valid' => true];
    }
}