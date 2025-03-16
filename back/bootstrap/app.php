<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php', // Ajout de la route API
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Ajout des middlewares DICT au groupe API
        $middleware->api([
            // Middlewares DICT
            \App\Http\Middleware\Dict\AvailabilityMiddleware::class,
            \App\Http\Middleware\Dict\IntegrityMiddleware::class,
            \App\Http\Middleware\Dict\ConfidentialityMiddleware::class,
            \App\Http\Middleware\Dict\TraceabilityMiddleware::class,
        ]);
        
        // Enregistrer les middlewares de route DICT
        $middleware->alias([
            'dict.availability' => \App\Http\Middleware\Dict\AvailabilityMiddleware::class,
            'dict.integrity' => \App\Http\Middleware\Dict\IntegrityMiddleware::class,
            'dict.confidentiality' => \App\Http\Middleware\Dict\ConfidentialityMiddleware::class,
            'dict.traceability' => \App\Http\Middleware\Dict\TraceabilityMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Configuration pour gérer les exceptions du système DICT
        $exceptions->reportable(function (\Exception $e) {
            // Journaliser les exceptions critiques dans le canal DICT
            if ($e instanceof \Illuminate\Database\QueryException) {
                \Illuminate\Support\Facades\Log::channel('dict')->critical('DICT:Disponibilité - Erreur de base de données', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            return false; // Permet à d'autres gestionnaires de continuer à traiter l'exception
        });
    })->create();