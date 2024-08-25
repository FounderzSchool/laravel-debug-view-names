# laravel-debug-view-names

Automatically add comments to indicate where a view starts and ends in rendered HTML in development mode.
Works for any rendered view: it will apply to the root view, `@include`d subviews, and components, at any level of nesting.

Considering this Blade component at `resources/views/components/container.blade.php`:

```html
<div class="container">
    <x-header />

    <x-main />

    <x-footer />
</div>
```

The rendered output will be the following, with comments indicating where each component/view starts and ends:

```html
<!-- Starting resources/views/components/container.blade.php -->
<div class="container">
    <!-- Starting resources/views/components/header.blade.php -->
    <header>
        <!-- ... -->
    </header>
    <!-- Ending resources/views/components/header.blade.php -->

    <!-- Starting resources/views/components/main.blade.php -->
    <main>
        <!-- ... -->
    </main>
    <!-- Ending resources/views/components/main.blade.php -->

    <!-- Starting resources/views/components/footer.blade.php -->
    <footer>
        <!-- ... -->
    </footer>
    <!-- Ending resources/views/components/footer.blade.php -->
</div>
<!-- Ending resources/views/components/container.blade.php -->
```

## Installation

You can install this package through composer:

```sh
composer require founderz/laravel-debug-view-names
```
