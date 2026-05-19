<?php

declare(strict_types=1);

namespace Founderz\LaravelDebugViewNames\Tests;

use Founderz\LaravelDebugViewNames\WrappedEngine;
use Illuminate\Contracts\View\Engine;
use PHPUnit\Framework\TestCase;

class WrappedEngineTest extends TestCase
{
    public function test_wraps_rendered_output_in_comments(): void
    {
        $wrapped = new WrappedEngine($this->stubEngine('BODY'), '/app');

        $this->assertSame(
            '<!-- Starting views/home.blade.php -->BODY<!-- Ending views/home.blade.php -->',
            $wrapped->get('/app/views/home.blade.php'),
        );
    }

    public function test_paths_under_basepath_are_stripped(): void
    {
        $wrapped = new WrappedEngine($this->stubEngine(''), '/app');

        $this->assertStringContainsString(
            '<!-- Starting views/home.blade.php -->',
            $wrapped->get('/app/views/home.blade.php'),
        );
    }

    public function test_paths_outside_basepath_are_left_intact(): void
    {
        $wrapped = new WrappedEngine($this->stubEngine(''), '/app');

        $this->assertStringContainsString(
            '<!-- Starting /elsewhere/home.blade.php -->',
            $wrapped->get('/elsewhere/home.blade.php'),
        );
    }

    public function test_partial_basepath_match_is_not_stripped(): void
    {
        // "/app2/foo" must not be matched by basepath "/app".
        $wrapped = new WrappedEngine($this->stubEngine(''), '/app');

        $this->assertStringContainsString(
            '<!-- Starting /app2/foo.blade.php -->',
            $wrapped->get('/app2/foo.blade.php'),
        );
    }

    public function test_forwards_unknown_method_calls_to_inner_engine(): void
    {
        $inner = new class () implements Engine {
            public function get($path, array $data = [])
            {
                return '';
            }

            public function customMethod(string $arg): string
            {
                return 'called:' . $arg;
            }
        };

        $wrapped = new WrappedEngine($inner, '/app');

        /** @phpstan-ignore-next-line method.notFound */
        $this->assertSame('called:hi', $wrapped->customMethod('hi'));
    }

    private function stubEngine(string $output): Engine
    {
        return new class ($output) implements Engine {
            public function __construct(private string $output)
            {
            }

            public function get($path, array $data = [])
            {
                return $this->output;
            }
        };
    }
}
