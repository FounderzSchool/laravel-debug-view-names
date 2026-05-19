# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

A small Laravel package (`founderz/laravel-debug-view-names`) that wraps Laravel's view engine resolver to emit `<!-- Starting path/to/view.blade.php -->` / `<!-- Ending ... -->` HTML comments around every rendered view (root views, `@include`d subviews, and components, at any nesting level). Intended for local/dev environments only.

## Commands

- `composer install` — install dependencies
- `vendor/bin/phpstan analyse` — static analysis (level 9, `src/` only; configured in `phpstan.neon`)
- `vendor/bin/pint` — code formatter (PSR-12 preset; see `pint.json`)

There is no test suite in this repo.

## Architecture

Three classes in `src/`, auto-discovered by Laravel via the `extra.laravel.providers` entry in `composer.json`:

- **`PackageServiceProvider`** — in `register()`, checks `laravel-debug-view-names.enable` and `laravel-debug-view-names.environments` (defaults to `['local']`). When active, replaces the `view.engine.resolver` container singleton with a `WrappedEngineResolver` and **sets `$this->app->instance('view', null)`** to force Laravel to rebuild the `Factory` with the new resolver. This reset is load-bearing: the `view` singleton normally captures the engine resolver at construction time (see the comment in `PackageServiceProvider::register_engine_resolver`), so without the reset the wrapping has no effect.

- **`WrappedEngineResolver`** — extends `Illuminate\View\Engines\EngineResolver` but composes the original resolver rather than replacing its internals. Re-implements `resolve()` to call the original's registered factory closures, wrap the result in a `WrappedEngine`, and cache the wrapped instance in the **original** resolver's `resolved` array (so both resolvers stay in sync). Note: `EngineResolver::$resolved` and `$resolvers` are accessed as public properties — this relies on the upstream Laravel class exposing them.

- **`WrappedEngine`** — implements `Illuminate\Contracts\View\Engine`. `get($path, $data)` delegates to the wrapped engine and sandwiches the output in HTML comments. The view path is stripped of the app's `basePath()` prefix so comments show relative paths. `__call` forwards any non-`get` method to the wrapped engine — needed because some engines (e.g. `CompilerEngine`) have additional public methods like `getCompiler()` that the framework calls directly.

### Working on this code

- Compatibility with Laravel's `EngineResolver` internals is fragile by design — any change to how Laravel constructs/caches engines or to the `view` singleton lifecycle could break the wrapping. When upgrading the `illuminate/*` constraints, re-verify against `ViewServiceProvider` and `EngineResolver` in the new version (these were byte-identical between 12.x and 13.x, but that won't always hold).
- PHPStan runs at level 9, so docblock array shapes (e.g. `array<mixed, mixed>`) are required on overridden methods to satisfy variance with the `Engine` contract.
