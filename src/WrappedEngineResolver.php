<?php

namespace Founderz\LaravelDebugViewNames;

use Illuminate\View\Engines\EngineResolver;
use Illuminate\Contracts\View\Engine;

class WrappedEngineResolver extends EngineResolver
{
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

    private function wrap(Engine $engine): WrappedEngine
    {
        return new WrappedEngine($engine, $this->base_path);
    }
}
