<?php

namespace Founderz\LaravelDebugViewNames;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\Contracts\View\Engine;

class PackageServiceProvider extends ServiceProvider
{
    public function register(): void {
        $engine_resolver = $this->app->make('view.engine.resolver');

        $base_path = $this->app->basePath();

        $this->app->singleton('view.engine.resolver', function () use ($engine_resolver, $base_path) {
            return new class($engine_resolver, $base_path) extends EngineResolver {
                public function __construct(private EngineResolver $original, private string $base_path) {}

                public function resolve($engine) {
                    if (isset($this->original->resolved[$engine])) {
                        return $this->original->resolved[$engine];
                    }

                    if (isset($this->original->resolvers[$engine])) {
                        return $this->original->resolved[$engine] = $this->wrap(call_user_func($this->original->resolvers[$engine]));
                    }

                    throw new \InvalidArgumentException("Engine [{$engine}] not found.");
                }

                public function wrap($engine) {
                    return new class($engine, $this->base_path) implements Engine {
                        public function __construct(private Engine $engine, private string $base_path) {}

                        public function get($path, array $data = []) {
                            $value = $this->engine->get($path, $data);

                            return $this->comment($path, true) . $value . $this->comment($path, false);
                        }

                        protected function comment(string $path, bool $start): string {
                            $base = $this->base_path . '/';
                            if (str_starts_with($path, $base)) {
                                $path = substr($path, strlen($base));
                            }

                            $starting = $start ? 'Starting' : 'Ending';
                            return '<!-- ' . $starting . ' ' . $path . ' -->';
                        }
                    };
                }
            };
        });

        // TODO im not sure what implications resetting this has
        // Resetting the view instance, so that next time it's gotten it uses our new engine resolver
        $this->app->instance('view', null);
    }
}
