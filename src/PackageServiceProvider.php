<?php

declare(strict_types=1);

namespace Founderz\LaravelDebugViewNames;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\EngineResolver;

class PackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-debug-view-names.php',
            'laravel-debug-view-names',
        );

        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->make('config');

        /** @var array<string> $environments */
        $environments = $config->get('laravel-debug-view-names.environments', ['local']);

        if (App::environment($environments) && $config->get('laravel-debug-view-names.enable')) {
            $this->registerEngineResolver();
        }
    }

    private function registerEngineResolver(): void
    {
        /** @var EngineResolver */
        $engineResolver = $this->app->make('view.engine.resolver');

        $basePath = $this->app->basePath();

        $this->app->singleton(
            'view.engine.resolver',
            fn () => new WrappedEngineResolver($engineResolver, $basePath),
        );

        // The `view` singleton (a \Illuminate\View\Factory) is created up-front
        // by ViewServiceProvider and captures the engine resolver at construction
        // time, so it has to be dropped from the container for our wrapped
        // resolver to take effect on the next `make('view')`. `instance(..., null)`
        // overwrites the cached instance; the next `make('view')` re-resolves
        // through the `view` binding (which is still registered).
        $this->app->instance('view', null);
    }

    public function boot(): void
    {
        $this->publishes(
            [__DIR__ . '/../config/laravel-debug-view-names.php' => $this->app->configPath('laravel-debug-view-names.php')],
            'laravel-debug-view-names-config',
        );
    }
}
