<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\Dict\DictAvailabilityService;
use App\Http\Services\Dict\DictIntegrityService;
use App\Http\Services\Dict\DictConfidentialityService;
use App\Http\Services\Dict\DictTraceabilityService;

class DictController extends Controller
{
    protected $availabilityService;
    protected $integrityService;
    protected $confidentialityService;
    protected $traceabilityService;
    
    /**
     * Constructeur avec injection de dépendance
     */
    public function __construct(
        DictAvailabilityService $availabilityService,
        DictIntegrityService $integrityService,
        DictConfidentialityService $confidentialityService,
        DictTraceabilityService $traceabilityService
    ) {
        $this->availabilityService = $availabilityService;
        $this->integrityService = $integrityService;
        $this->confidentialityService = $confidentialityService;
        $this->traceabilityService = $traceabilityService;
    }
    
    /**
     * Vérifier l'état de santé du système
     */
    public function health()
    {
        return response()->json($this->availabilityService->checkSystemHealth());
    }
    
    /**
     * Vérifier l'intégrité du système
     */
    public function integrity()
    {
        return response()->json($this->integrityService->checkDatabaseIntegrity());
    }
    
    /**
     * Obtenir le rapport de confidentialité
     */
    public function confidentiality()
    {
        return response()->json($this->confidentialityService->getConfidentialityReport());
    }
    
    /**
     * Obtenir les statistiques d'activité
     */
    public function activity(Request $request)
    {
        $minutes = $request->input('minutes', 60);
        return response()->json($this->traceabilityService->getActivityReport($minutes));
    }
    
    /**
     * Obtenir les statistiques générales DICT
     */
    public function stats()
    {
        return response()->json([
            'request_count' => \Cache::get('dict.request_count', 0),
            'average_response_time' => \Cache::get('dict.avg_response_time', 0),
            'error_count' => \Cache::get('dict.error_count', 0),
            'system_health' => \Cache::get('dict.system_health', 'unknown')
        ]);
    }
    
    /**
     * Dashboard DICT (pourrait retourner une vue HTML)
     */
    public function dashboard()
    {
        // Pour l'API, on retourne juste les données
        $data = [
            'health' => $this->availabilityService->checkSystemHealth(),
            'integrity' => $this->integrityService->checkDatabaseIntegrity(),
            'confidentiality' => $this->confidentialityService->getConfidentialityReport(),
            'activity' => $this->traceabilityService->getActivityReport(60),
            'stats' => [
                'request_count' => \Cache::get('dict.request_count', 0),
                'average_response_time' => \Cache::get('dict.avg_response_time', 0),
                'error_count' => \Cache::get('dict.error_count', 0)
            ]
        ];
        
        return response()->json($data);
    }
}