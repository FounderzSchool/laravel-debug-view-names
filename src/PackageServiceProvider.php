<?php

namespace Founderz\LaravelDebugViewNames;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\EngineResolver;

class PackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app->make('config');

        if (App::environment($config->get('laravel-debug-view-names.environments')) && $config->get('laravel-debug-view-names.enable')) {
            $this->register_engine_resolver();
        }
    }

    function register_engine_resolver(): void {
        /** @var EngineResolver */
        $engine_resolver = $this->app->make('view.engine.resolver');

        $base_path = $this->app->basePath();

        $this->app->singleton(
            'view.engine.resolver',
            fn () =>
            new WrappedEngineResolver($engine_resolver, $base_path)
        );

        // Resetting the view instance, so that next time it's gotten,
        // it's loaded with our new engine resolver.
        //
        // Currently, the `'view'` singleton (a \Illuminate\View\Factory)
        // is created at the start, and it takes an Engine resolver that
        // it loads at the start and keeps loaded:
        // https://github.com/laravel/framework/blob/500f3eb8970ed2a0bf6c31d6db2f02932b46cd12/src/Illuminate/View/ViewServiceProvider.php#L43
        //
        // By resetting the `'view'` instance, we force Laravel to re-create it,
        // which will now use the `'view.engine.resolver'` singleton we defined above.
        //
        // I'm not sure what implications resetting this has,
        // but it seems to work correctly so far.
        $this->app->instance('view', null);
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-debug-view-names.php' => $this->app->configPath('laravel-debug-view-names.php'),
        ]);
    }
}
