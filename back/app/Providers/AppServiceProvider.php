<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Services\Dict\DictAvailabilityService;
use App\Http\Services\Dict\DictIntegrityService;
use App\Http\Services\Dict\DictConfidentialityService;
use App\Http\Services\Dict\DictTraceabilityService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrer les services DICT comme singletons
        $this->app->singleton(DictAvailabilityService::class, function ($app) {
            return new DictAvailabilityService();
        });
        
        $this->app->singleton(DictIntegrityService::class, function ($app) {
            return new DictIntegrityService();
        });
        
        $this->app->singleton(DictConfidentialityService::class, function ($app) {
            return new DictConfidentialityService();
        });
        
        $this->app->singleton(DictTraceabilityService::class, function ($app) {
            return new DictTraceabilityService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurer Laravel Schema pour MySQL
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
    }
}