<?php

namespace Modules\Geofence\app\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Geofence';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapWebhookRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path($this->name, '/routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->name('api.')
            ->group(module_path($this->name, '/routes/api.php'));
    }

    /**
     * Define the webhook routes for the application.
     *
     * These routes don't have CSRF protection and use signature verification instead.
     */
    protected function mapWebhookRoutes(): void
    {
        Route::middleware('api')
            ->prefix('webhooks')
            ->name('webhooks.')
            ->group(module_path($this->name, '/routes/webhook.php'));
    }
}
