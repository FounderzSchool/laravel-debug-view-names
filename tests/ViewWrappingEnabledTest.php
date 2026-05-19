<?php

declare(strict_types=1);

namespace Founderz\LaravelDebugViewNames\Tests;

use Founderz\LaravelDebugViewNames\PackageServiceProvider;
use Illuminate\Contracts\View\Engine;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase;

class ViewWrappingEnabledTest extends TestCase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return [PackageServiceProvider::class];
    }

    /**
     * Set config BEFORE providers register — the package reads its config
     * eagerly in `register()`, so `defineEnvironment` (which runs after
     * provider registration in testbench) is too late.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('laravel-debug-view-names.enable', true);
        $app['config']->set('laravel-debug-view-names.environments', ['testing']);
        $app['config']->set('view.paths', [__DIR__ . '/fixtures/views']);
    }

    public function test_root_view_is_wrapped_in_comments(): void
    {
        $output = View::make('parent')->render();

        $this->assertStringContainsString('<!-- Starting ', $output);
        $this->assertStringContainsString('parent.blade.php -->', $output);
        $this->assertStringContainsString('<!-- Ending ', $output);
    }

    public function test_included_views_are_wrapped_inside_their_parent(): void
    {
        $output = View::make('parent')->render();

        $parentStart = strpos($output, '<!-- Starting ');
        $childStart = strpos($output, '<!-- Starting ', (int) $parentStart + 1);
        $parentEnd = strrpos($output, '<!-- Ending ');

        $this->assertNotFalse($parentStart);
        $this->assertNotFalse($childStart);
        $this->assertNotFalse($parentEnd);
        $this->assertStringContainsString('child.blade.php -->', $output);
        // Child opening comes after parent opening; parent closing comes last.
        $this->assertGreaterThan($parentStart, $childStart);
        $this->assertGreaterThan($childStart, $parentEnd);
    }

    public function test_custom_engine_registered_through_resolver_is_wrapped(): void
    {
        /** @var \Illuminate\View\Engines\EngineResolver $resolver */
        $resolver = $this->app->make('view.engine.resolver');

        $resolver->register('custom-test', fn () => new class () implements Engine {
            public function get($path, array $data = [])
            {
                return 'INNER';
            }
        });

        $engine = $resolver->resolve('custom-test');
        $output = $engine->get('/tmp/some-view.blade.custom');

        $this->assertStringContainsString('INNER', $output);
        $this->assertStringContainsString('<!-- Starting /tmp/some-view.blade.custom -->', $output);
        $this->assertStringContainsString('<!-- Ending /tmp/some-view.blade.custom -->', $output);
    }
}
