<?php

namespace Modules\BankOffer\app\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'BankOffer';

    /**
     * Called before routes are registered.
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
        $this->mapAdminRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "api" routes for the application.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->name('api.')
            ->group(module_path($this->name, '/routes/api.php'));
    }

    /**
     * Define the "admin" routes for the application.
     */
    protected function mapAdminRoutes(): void
    {
        $adminRoutesPath = module_path($this->name, '/routes/admin.php');

        if (file_exists($adminRoutesPath)) {
            Route::middleware(['web', 'auth', 'role:admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group($adminRoutesPath);
        }
    }

    /**
     * Define the "web" routes for the application.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path($this->name, '/routes/web.php'));
    }
}
