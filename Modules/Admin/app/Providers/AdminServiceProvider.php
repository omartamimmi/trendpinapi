<?php

namespace Modules\Admin\app\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Admin\app\Repositories\Contracts\OnboardingRepositoryInterface;
use Modules\Admin\app\Repositories\Contracts\PlanRepositoryInterface;
use Modules\Admin\app\Repositories\Contracts\UserRepositoryInterface;
use Modules\Admin\app\Repositories\OnboardingRepository;
use Modules\Admin\app\Repositories\PlanRepository;
use Modules\Admin\app\Repositories\UserRepository;
use Modules\Admin\app\Services\AuthService;
use Modules\Admin\app\Services\Contracts\AuthServiceInterface;
use Modules\Admin\app\Services\Contracts\DashboardServiceInterface;
use Modules\Admin\app\Services\Contracts\OnboardingServiceInterface;
use Modules\Admin\app\Services\Contracts\PlanServiceInterface;
use Modules\Admin\app\Services\Contracts\UserServiceInterface;
use Modules\Admin\app\Services\DashboardService;
use Modules\Admin\app\Services\OnboardingService;
use Modules\Admin\app\Services\PlanService;
use Modules\Admin\app\Services\UserService;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AdminServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Admin';

    protected string $nameLower = 'admin';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Register repository bindings.
     */
    protected function registerRepositories(): void
    {
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository(new User());
        });

        $this->app->bind(PlanRepositoryInterface::class, function ($app) {
            return new PlanRepository(new SubscriptionPlan());
        });

        $this->app->bind(OnboardingRepositoryInterface::class, function ($app) {
            return new OnboardingRepository(new RetailerOnboarding());
        });
    }

    /**
     * Register service bindings.
     */
    protected function registerServices(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(PlanServiceInterface::class, PlanService::class);
        $this->app->bind(OnboardingServiceInterface::class, OnboardingService::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
