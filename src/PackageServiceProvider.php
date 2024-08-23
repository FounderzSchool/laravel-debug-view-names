<?php

namespace Founderz\LaravelDebugViewNames;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\EngineResolver;

class PackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var EngineResolver */
        $engine_resolver = $this->app->make('view.engine.resolver');

        $base_path = $this->app->basePath();

        $this->app->singleton(
            'view.engine.resolver',
            fn () =>
            new WrappedEngineResolver($engine_resolver, $base_path)
        );

        // TODO im not sure what implications resetting this has
        // Resetting the view instance, so that next time it's gotten it uses our new engine resolver
        $this->app->instance('view', null);
    }
}
