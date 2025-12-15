<?php

namespace Modules\Log\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Log\app\Models\ActivityLog;
use Modules\Log\app\Repositories\Contracts\LogRepositoryInterface;
use Modules\Log\app\Repositories\LogRepository;
use Modules\Log\app\Services\Contracts\LogServiceInterface;
use Modules\Log\app\Services\LogService;

class LogServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Log';
    protected string $moduleNameLower = 'log';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        // Bind Repository
        $this->app->bind(LogRepositoryInterface::class, function ($app) {
            return new LogRepository(new ActivityLog());
        });

        // Bind Service
        $this->app->bind(LogServiceInterface::class, LogService::class);
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Log\app\Console\Commands\CleanupLogsCommand::class,
            ]);
        }
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );
    }

    public function provides(): array
    {
        return [
            LogRepositoryInterface::class,
            LogServiceInterface::class,
        ];
    }
}
