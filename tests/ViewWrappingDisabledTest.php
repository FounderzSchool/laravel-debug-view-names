<?php

declare(strict_types=1);

namespace Founderz\LaravelDebugViewNames\Tests;

use Founderz\LaravelDebugViewNames\PackageServiceProvider;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase;

class ViewWrappingDisabledTest extends TestCase
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
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('laravel-debug-view-names.enable', false);
        $app['config']->set('laravel-debug-view-names.environments', ['testing']);
        $app['config']->set('view.paths', [__DIR__ . '/fixtures/views']);
    }

    public function test_no_comments_are_emitted_when_disabled(): void
    {
        $output = View::make('parent')->render();

        $this->assertStringNotContainsString('<!-- Starting', $output);
        $this->assertStringNotContainsString('<!-- Ending', $output);
    }
}
