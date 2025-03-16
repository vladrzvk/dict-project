<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DictController;

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

// Routes pour les articles (CRUD) avec les middlewares DICT
Route::middleware('dict')->group(function () {
    Route::apiResource('articles', ArticleController::class);
});

// Routes pour le DICT (surveillance)
Route::prefix('dict')->group(function () {
    // Utilisation du nouveau DictController au lieu des routes closure
    Route::get('/health', [DictController::class, 'health']);
    Route::get('/integrity', [DictController::class, 'integrity']);
    Route::get('/confidentiality', [DictController::class, 'confidentiality']);
    Route::get('/activity', [DictController::class, 'activity']);
    Route::get('/stats', [DictController::class, 'stats']);
    Route::get('/dashboard', [DictController::class, 'dashboard']);
});

// Inclure routes/dict.php si le fichier existe (pour d'éventuelles extensions)
if (file_exists(base_path('routes/dict.php'))) {
    require base_path('routes/dict.php');
}