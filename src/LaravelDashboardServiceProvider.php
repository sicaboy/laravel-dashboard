<?php

namespace Sicaboy\LaravelDashboard;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelDashboardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-dashboard.php' => config_path('laravel-dashboard.php'),
        ], 'laravel-dashboard-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerRoutes();
    }


    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
//            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Get the Nova route group configuration array.
     *
     * @return array
     */
    protected function routeConfiguration(): array
    {
        return [
            'namespace' => 'Sicaboy\LaravelDashboard\Http\Controllers',
            'prefix' => 'dashboard',
            'as' => 'dashboard.',
            'middleware' => 'web',
        ];
    }
}
