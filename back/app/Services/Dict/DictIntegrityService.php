<?php

namespace App\Http\Services\Dict;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class DictIntegrityService
{
    /**
     * Valider des données d'entrée selon des règles définies
     * 
     * @param array $data Les données à valider
     * @param array $rules Les règles de validation
     * @return array Résultat de la validation
     */
    public function validateData(array $data, array $rules)
    {
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            Log::warning('DICT:Intégrité - Données invalides', ['errors' => $validator->errors()->toArray()]);
            
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray()
            ];
        }
        
        return [
            'valid' => true,
            'data' => $data
        ];
    }
    
    /**
     * Vérifier l'intégrité de la base de données
     * 
     * @return array Résultat de la vérification
     */
    public function checkDatabaseIntegrity()
    {
        $results = ['status' => 'healthy', 'issues' => []];
        
        try {
            // Vérifier la structure des tables
            $this->checkTableStructure('articles', $results);
            
            // Vérifier les contraintes d'intégrité
            $this->checkForeignKeys($results);
            
            // Vérifier la cohérence des données
            $this->checkDataConsistency($results);
        } catch (\Exception $e) {
            Log::error('DICT:Intégrité - Erreur lors de la vérification: ' . $e->getMessage());
            $results['status'] = 'error';
            $results['issues'][] = [
                'type' => 'exception',
                'message' => $e->getMessage()
            ];
        }
        
        return [
            'timestamp' => now()->toIso8601String(),
            'status' => $results['status'],
            'checks_performed' => count($results['issues']) == 0,
            'issues' => $results['issues']
        ];
    }
    
    /**
     * Vérifier la structure d'une table
     * 
     * @param string $table Nom de la table
     * @param array &$results Résultats de la vérification
     * @return void
     */
    private function checkTableStructure($table, &$results)
    {
        if (!Schema::hasTable($table)) {
            $results['status'] = 'degraded';
            $results['issues'][] = [
                'type' => 'missing_table',
                'table' => $table,
                'message' => "La table '$table' n'existe pas"
            ];
            return;
        }
        
        // Vérifier les colonnes attendues
        $expectedColumns = [
            'articles' => ['id', 'title', 'content', 'author', 'published', 'created_at', 'updated_at']
        ];
        
        if (isset($expectedColumns[$table])) {
            $missingColumns = [];
            
            foreach ($expectedColumns[$table] as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    $missingColumns[] = $column;
                }
            }
            
            if (!empty($missingColumns)) {
                $results['status'] = 'degraded';
                $results['issues'][] = [
                    'type' => 'missing_columns',
                    'table' => $table,
                    'columns' => $missingColumns,
                    'message' => "Colonnes manquantes dans la table '$table': " . implode(', ', $missingColumns)
                ];
            }
        }
    }
    
    /**
     * Vérifier les contraintes d'intégrité référentielle
     * 
     * @param array &$results Résultats de la vérification
     * @return void
     */
    private function checkForeignKeys(&$results)
    {
        // Dans cet exemple, il n'y a pas de contraintes de clé étrangère,
        // mais cette méthode pourrait être étendue pour les futures relations
    }
    
    /**
     * Vérifier la cohérence des données
     * 
     * @param array &$results Résultats de la vérification
     * @return void
     */
    private function checkDataConsistency(&$results)
    {
        try {
            // Vérifier les articles avec des titres dupliqués
            $duplicates = DB::table('articles')
                ->select('title', DB::raw('COUNT(*) as count'))
                ->groupBy('title')
                ->having('count', '>', 1)
                ->get();
            
            if ($duplicates->isNotEmpty()) {
                $results['status'] = 'warning';
                $results['issues'][] = [
                    'type' => 'duplicate_data',
                    'message' => 'Titres d\'articles dupliqués détectés',
                    'count' => $duplicates->count()
                ];
            }
            
            // Vérifier les articles sans contenu
            $emptyContent = DB::table('articles')
                ->whereNull('content')
                ->orWhere('content', '')
                ->count();
            
            if ($emptyContent > 0) {
                $results['status'] = 'warning';
                $results['issues'][] = [
                    'type' => 'empty_content',
                    'message' => 'Articles sans contenu détectés',
                    'count' => $emptyContent
                ];
            }
        } catch (\Exception $e) {
            // En cas d'erreur, on considère qu'on ne peut pas vérifier la cohérence
            Log::error('DICT:Intégrité - Erreur lors de la vérification de cohérence: ' . $e->getMessage());
        }
    }
}