<?php

declare(strict_types=1);

namespace Founderz\LaravelDebugViewNames;

use Closure;
use Illuminate\Contracts\View\Engine;
use Illuminate\View\Engines\EngineResolver;

class WrappedEngineResolver extends EngineResolver
{
    public function __construct(private EngineResolver $original, private string $basePath)
    {
    }

    /**
     * @param  string  $engine
     */
    public function register($engine, Closure $resolver)
    {
        $this->original->register($engine, $resolver);
    }

    /**
     * @param  string  $engine
     */
    public function resolve($engine)
    {
        // We manually re-implement `resolve`, since we want to store
        // the wrapped resolved engine, instead of re-wrapping it every time.

        if (isset($this->original->resolved[$engine])) {
            return $this->original->resolved[$engine];
        }

        if (isset($this->original->resolvers[$engine])) {
            /** @var Engine */
            $resolvedEngine = call_user_func($this->original->resolvers[$engine]);
            return $this->original->resolved[$engine] = $this->wrap($resolvedEngine);
        }

        throw new \InvalidArgumentException("Engine [{$engine}] not found.");
    }

    /**
     * @param  string  $engine
     */
    public function forget($engine)
    {
        $this->original->forget($engine);
    }

    private function wrap(Engine $engine): WrappedEngine
    {
        return new WrappedEngine($engine, $this->basePath);
    }
}
