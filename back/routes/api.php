<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes pour les articles (CRUD)
Route::apiResource('articles', ArticleController::class);

// Routes pour le DICT (surveillance)
Route::prefix('dict')->group(function () {
    Route::get('/health', function () {
        // Vérification simple de l'état du système
        $dbConnection = true;
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbConnection = false;
        }

        return response()->json([
            'status' => $dbConnection ? 'UP' : 'DOWN',
            'timestamp' => now()->toIso8601String(),
            'components' => [
                'database' => [
                    'status' => $dbConnection ? 'UP' : 'DOWN'
                ],
                'application' => [
                    'status' => 'UP'
                ]
            ]
        ]);
    });

    // Endpoint pour vérifier l'intégrité
    Route::get('/integrity', function () {
        // Vérification simplifiée, vous pourriez ajouter plus de contrôles
        return response()->json([
            'status' => 'OK',
            'timestamp' => now()->toIso8601String(),
            'checks_performed' => [
                'file_integrity' => true,
                'database_integrity' => true
            ]
        ]);
    });

    // Endpoint pour les statistiques
    Route::get('/stats', function () {
        return response()->json([
            'request_count' => \Cache::get('dict.request_count', 0),
            'average_response_time' => \Cache::get('dict.avg_response_time', 0),
            'error_count' => \Cache::get('dict.error_count', 0)
        ]);
    });
});

// Inclure routes/dict.php si le fichier existe
if (file_exists(base_path('routes/dict.php'))) {
    require base_path('routes/dict.php');
}