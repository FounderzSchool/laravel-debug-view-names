<?php

namespace Founderz\LaravelDebugViewNames;

use Illuminate\Contracts\View\Engine;

class WrappedEngine implements Engine
{
    public function __construct(private Engine $engine, private string $base_path)
    {
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array<mixed, mixed>  $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        $value = $this->engine->get($path, $data);

        return $this->comment($path, true) . $value . $this->comment($path, false);
    }

    /**
     * Return an HTML comment that indicates the path of the view.
     *
     * @param  string  $path
     * @param  bool  $opening Whether it's the opening comment.
     * @return string
     */
    protected function comment(string $path, bool $opening): string
    {
        $base = $this->base_path . '/';
        if (str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        $starting = $opening ? 'Starting' : 'Ending';
        return '<!-- ' . $starting . ' ' . $path . ' -->';
    }

    /**
     * Handle dynamic method calls into the engine instance.
     *
     * @param  string  $method
     * @param  array<mixed>  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->engine->$method(...$parameters);
    }
}
