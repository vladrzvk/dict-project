<?php

namespace App\Http\Services\Dict;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DictTraceabilityService
{
    /**
     * Générer un identifiant unique pour la requête
     * 
     * @return string
     */
    public function generateRequestId()
    {
        return (string) Str::uuid();
    }
    
    /**
     * Journaliser une requête entrante
     * 
     * @param Request $request
     * @param string $requestId
     * @return void
     */
    public function logIncomingRequest(Request $request, string $requestId)
    {
        $data = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'referer' => $request->header('Referer'),
            'timestamp' => now()->toIso8601String()
        ];
        
        // Ajouter l'utilisateur authentifié si disponible
        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
            $data['user_email'] = $request->user()->email;
        }
        
        Log::channel('dict')->info('DICT:Traçabilité - Requête reçue', $data);
        
        // Incrémenter le compteur de requêtes
        $this->incrementRequestCounter();
        
        // Enregistrer la requête dans la base de données si souhaité
        if (config('dict.store_requests_in_db', false)) {
            $this->storeRequestInDatabase($data);
        }
    }
    
    /**
     * Journaliser une réponse sortante
     * 
     * @param mixed $response
     * @param string $requestId
     * @param float $startTime
     * @return void
     */
    public function logOutgoingResponse($response, string $requestId, float $startTime)
    {
        $duration = microtime(true) - $startTime;
        $statusCode = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200;
        
        $data = [
            'request_id' => $requestId,
            'status' => $statusCode,
            'duration_ms' => round($duration * 1000, 2),
            'timestamp' => now()->toIso8601String()
        ];
        
        // Journaliser différemment selon le code d'état
        if ($statusCode >= 400 && $statusCode < 500) {
            Log::channel('dict')->warning('DICT:Traçabilité - Réponse erreur client', $data);
            $this->incrementErrorCounter();
        } elseif ($statusCode >= 500) {
            Log::channel('dict')->error('DICT:Traçabilité - Réponse erreur serveur', $data);
            $this->incrementErrorCounter();
        } else {
            Log::channel('dict')->info('DICT:Traçabilité - Réponse envoyée', $data);
        }
        
        // Mettre à jour le temps de réponse moyen
        $this->updateAverageResponseTime($duration * 1000);
    }
    
    /**
     * Incrémenter le compteur de requêtes
     * 
     * @return void
     */
    private function incrementRequestCounter()
    {
        $count = \Cache::get('dict.request_count', 0);
        \Cache::put('dict.request_count', $count + 1);
    }
    
    /**
     * Incrémenter le compteur d'erreurs
     * 
     * @return void
     */
    private function incrementErrorCounter()
    {
        $count = \Cache::get('dict.error_count', 0);
        \Cache::put('dict.error_count', $count + 1);
    }
    
    /**
     * Mettre à jour le temps de réponse moyen
     * 
     * @param float $duration Durée en ms
     * @return void
     */
    private function updateAverageResponseTime(float $duration)
    {
        $avg = \Cache::get('dict.avg_response_time', 0);
        $count = \Cache::get('dict.request_count', 1);
        
        // Moyenne mobile pondérée
        $newAvg = ($avg * ($count - 1) + $duration) / $count;
        \Cache::put('dict.avg_response_time', $newAvg);
    }
    
    /**
     * Stocker les informations de requête dans la base de données
     * 
     * @param array $data
     * @return void
     */
    private function storeRequestInDatabase(array $data)
    {
        try {
            // Implémentation à adapter selon la structure de votre base de données
            // Cette fonction est laissée vide car elle dépend de votre schéma
            // Exemple:
            // DB::table('dict_request_logs')->insert([
            //     'request_id' => $data['request_id'],
            //     'method' => $data['method'],
            //     'url' => $data['url'],
            //     'ip' => $data['ip'],
            //     'user_agent' => $data['user_agent'],
            //     'user_id' => $data['user_id'] ?? null,
            //     'created_at' => now()
            // ]);
        } catch (\Exception $e) {
            Log::error('DICT:Traçabilité - Erreur lors de l\'enregistrement dans la base de données', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtenir un rapport d'activité
     * 
     * @param int $minutes Minutes à inclure dans le rapport
     * @return array
     */
    public function getActivityReport($minutes = 60)
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'period' => $minutes . ' minutes',
            'request_count' => \Cache::get('dict.request_count', 0),
            'error_count' => \Cache::get('dict.error_count', 0),
            'average_response_time_ms' => round(\Cache::get('dict.avg_response_time', 0), 2)
        ];
    }
}