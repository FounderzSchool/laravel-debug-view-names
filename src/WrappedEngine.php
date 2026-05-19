<?php

declare(strict_types=1);

namespace Founderz\LaravelDebugViewNames;

use Illuminate\Contracts\View\Engine;

class WrappedEngine implements Engine
{
    private string $basePrefix;

    public function __construct(private Engine $engine, string $basePath)
    {
        $this->basePrefix = $basePath . '/';
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array<string, mixed>  $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        $value = $this->engine->get($path, $data);

        return $this->comment($path, true) . $value . $this->comment($path, false);
    }

    /**
     * Return an HTML comment that indicates the path of the view.
     */
    private function comment(string $path, bool $opening): string
    {
        if (str_starts_with($path, $this->basePrefix)) {
            $path = substr($path, strlen($this->basePrefix));
        }

        $starting = $opening ? 'Starting' : 'Ending';

        return '<!-- ' . $starting . ' ' . $path . ' -->';
    }

    /**
     * Handle dynamic method calls into the engine instance.
     *
     * @param  string  $method
     * @param  array<int, mixed>  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->engine->$method(...$parameters);
    }
}
