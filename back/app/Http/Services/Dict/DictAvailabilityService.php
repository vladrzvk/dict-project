<?php

namespace App\Http\Services\Dict;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DictAvailabilityService
{
    /**
     * Vérifier l'état du système
     * 
     * @return array
     */
    public function checkSystemHealth()
    {
        $dbStatus = $this->checkDatabaseConnection();
        $cacheStatus = $this->checkCacheConnection();
        $diskSpace = $this->checkDiskSpace();
        
        $overallStatus = ($dbStatus && $cacheStatus && $diskSpace > 10) ? 'healthy' : 'degraded';
        
        // Mise en cache des résultats pour 5 minutes
        Cache::put('dict.system_health', $overallStatus, 300);
        
        return [
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'components' => [
                'database' => [
                    'status' => $dbStatus ? 'UP' : 'DOWN',
                    'details' => $dbStatus ? 'Connexion établie' : 'Échec de connexion'
                ],
                'cache' => [
                    'status' => $cacheStatus ? 'UP' : 'DOWN'
                ],
                'disk' => [
                    'status' => $diskSpace > 10 ? 'UP' : 'WARNING',
                    'free_space_gb' => $diskSpace
                ],
                'memory' => [
                    'status' => 'UP',
                    'usage_percent' => $this->getMemoryUsage()
                ]
            ]
        ];
    }
    
    /**
     * Vérifier la connexion à la base de données
     * 
     * @return bool
     */
    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('DICT:Disponibilité - Erreur de connexion DB: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier la connexion au cache
     * 
     * @return bool
     */
    private function checkCacheConnection()
    {
        try {
            Cache::put('dict.test', true, 1);
            return Cache::get('dict.test', false);
        } catch (\Exception $e) {
            Log::error('DICT:Disponibilité - Erreur de cache: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier l'espace disque disponible
     * 
     * @return float Espace libre en GB
     */
    private function checkDiskSpace()
    {
        $freeSpace = disk_free_space(storage_path());
        $freeSpaceGB = round($freeSpace / 1024 / 1024 / 1024, 2);
        
        return $freeSpaceGB;
    }
    
    /**
     * Obtenir le pourcentage d'utilisation de la mémoire
     * 
     * @return float
     */
    private function getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            $memoryUsed = memory_get_usage(true);
            // Estimation approximative, à ajuster selon la configuration du serveur
            $memoryLimit = $this->getMemoryLimitInBytes();
            
            if ($memoryLimit > 0) {
                return round(($memoryUsed / $memoryLimit) * 100, 2);
            }
        }
        
        return 0;
    }
    
    /**
     * Obtenir la limite de mémoire en octets
     * 
     * @return int
     */
    private function getMemoryLimitInBytes()
    {
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit === '-1') {
            // Illimité
            return PHP_INT_MAX;
        }
        
        $value = (int) $memoryLimit;
        $unit = strtolower(substr($memoryLimit, -1));
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
}