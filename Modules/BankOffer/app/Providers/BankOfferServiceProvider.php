<?php

namespace Modules\BankOffer\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\BankOffer\Repositories\Contracts\BankRepositoryInterface;
use Modules\BankOffer\Repositories\BankRepository;
use Modules\BankOffer\Repositories\Contracts\CardTypeRepositoryInterface;
use Modules\BankOffer\Repositories\CardTypeRepository;
use Modules\BankOffer\Repositories\Contracts\BankOfferRepositoryInterface;
use Modules\BankOffer\Repositories\BankOfferRepository;
use Modules\BankOffer\Services\Contracts\BankOfferServiceInterface;
use Modules\BankOffer\Services\BankOfferService;

class BankOfferServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'BankOffer';

    protected string $moduleNameLower = 'bankoffer';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Register repositories
     */
    protected function registerRepositories(): void
    {
        $this->app->bind(BankRepositoryInterface::class, BankRepository::class);
        $this->app->bind(CardTypeRepositoryInterface::class, CardTypeRepository::class);
        $this->app->bind(BankOfferRepositoryInterface::class, BankOfferRepository::class);
    }

    /**
     * Register services
     */
    protected function registerServices(): void
    {
        $this->app->bind(BankOfferServiceInterface::class, BankOfferService::class);
    }

    /**
     * Register commands
     */
    protected function registerCommands(): void
    {
        // Register custom artisan commands
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Get publishable view paths
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];

        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }

        return $paths;
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            BankRepositoryInterface::class,
            CardTypeRepositoryInterface::class,
            BankOfferRepositoryInterface::class,
            BankOfferServiceInterface::class,
        ];
    }
}
