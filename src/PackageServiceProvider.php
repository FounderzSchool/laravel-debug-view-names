<?php

namespace Founderz\LaravelDebugViewNames;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\Contracts\View\Engine;

class PackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var EngineResolver */
        $engine_resolver = $this->app->make('view.engine.resolver');

        $base_path = $this->app->basePath();

        $this->app->singleton('view.engine.resolver', function () use ($engine_resolver, $base_path) {
            return new class ($engine_resolver, $base_path) extends EngineResolver {
                public function __construct(private EngineResolver $original, private string $base_path)
                {
                }

                public function resolve($engine)
                {
                    // We manually re-implement `resolve`, since we want to store
                    // the wrapped resolved engine, instead of re-wrapping it every time.

                    if (isset($this->original->resolved[$engine])) {
                        return $this->original->resolved[$engine];
                    }

                    if (isset($this->original->resolvers[$engine])) {
                        /** @var Engine */
                        $resolved_engine = call_user_func($this->original->resolvers[$engine]);
                        return $this->original->resolved[$engine] = $this->wrap($resolved_engine);
                    }

                    throw new \InvalidArgumentException("Engine [{$engine}] not found.");
                }

                public function wrap(Engine $engine): WrappedEngine
                {
                    return new WrappedEngine($engine, $this->base_path);
                }
            };
        });

        // TODO im not sure what implications resetting this has
        // Resetting the view instance, so that next time it's gotten it uses our new engine resolver
        $this->app->instance('view', null);
    }
}
