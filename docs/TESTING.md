# Testing

PressGang ships with a unit test suite so you can verify framework behaviour and safely refactor without a running WordPress installation.

## Stack

- **PHPUnit 9.6** — test runner
- **yoast/wp-test-utils ^1.2** — provides BrainMonkey integration and pre-stubbed WordPress functions (matches Timber 2's own test stack)
- **BrainMonkey** — mocks WordPress functions (`add_action`, `apply_filters`, `wp_cache_get`, etc.) in pure PHP

No WordPress database, no web server, no Docker required.

## Running Tests

```bash
composer test            # alias for test:unit
composer test:unit       # run the full unit suite
vendor/bin/phpunit --filter ConfigTest           # run a single test class
vendor/bin/phpunit --filter loads_and_caches     # run a single test by name
vendor/bin/phpunit --list-tests                  # list all discovered tests
```

## Directory Structure

Tests mirror the `src/` layout under `tests/Unit/`:

```
tests/
├── bootstrap.php              # autoloader + THEMENAME/ABSPATH constants
└── Unit/
    ├── TestCase.php           # base class (extends YoastTestCase)
    ├── Blocks/                # BlockClassManager
    ├── Bootstrap/             # Config, FileConfigLoader, Loader
    ├── Configuration/         # Sidebars, Menus, CustomPostTypes, Actions
    ├── ContextManagers/       # Menu, Site, ThemeMods, AcfOptions, WooCommerce
    └── ServiceProviders/      # TimberServiceProvider
```

## Writing a New Test

### 1. Create the test class

Place it under `tests/Unit/` mirroring the `src/` path. For example, a test for `src/Configuration/Sidebars.php` goes in `tests/Unit/Configuration/SidebarsTest.php`.

### 2. Extend the base TestCase

```php
namespace PressGang\Tests\Unit\Configuration;

use PressGang\Tests\Unit\TestCase;

class SidebarsTest extends TestCase {
    // ...
}
```

The base `TestCase` extends `Yoast\WPTestUtils\BrainMonkey\YoastTestCase`, which handles BrainMonkey setup and teardown automatically. It also provides:

- `resetSingletonInstances()` — clears `ConfigurationSingleton` state between tests
- `setPostData()` / `clearPostData()` — helpers for testing form validators

### 3. Mock WordPress functions with BrainMonkey

```php
use Brain\Monkey\Functions;

/** @test */
public function registers_sidebars_from_config(): void {
    Functions\expect('register_sidebar')
        ->once()
        ->with(\Mockery::on(fn($args) => $args['id'] === 'main'));

    // trigger the code under test...
}
```

### 4. Reset singletons when needed

Any test that touches a `ConfigurationSingleton` subclass should reset state:

```php
public function set_up(): void {
    parent::set_up();
    $this->resetSingletonInstances();
}
```

## Testing Context Managers

Context managers depend on static calls (`Timber::get_menu()`, `new Site()`) and global helpers (`config()`) that cannot be mocked directly with BrainMonkey. PressGang uses the **protected method pattern** — static calls are wrapped in protected methods that tests override via anonymous subclasses:

```php
// Production class
class MenuContextManager implements ContextManagerInterface {
    protected function get_menu(string $location): ?object {
        return Timber::get_menu($location);
    }
}

// Test
private function makeManager(): MenuContextManager {
    return new class(['primary' => $menuStub]) extends MenuContextManager {
        public function __construct(private readonly array $menuMap) {}
        protected function get_menu(string $location): ?object {
            return $this->menuMap[$location] ?? null;
        }
    };
}
```

This avoids `@runTestsInSeparateProcesses` (which is 5-10x slower) and keeps tests fast and deterministic.

## Tips and Gotchas

### BrainMonkey `apply_filters` signature

`apply_filters` receives `($hook, $value, ...$extra)`. To pass through the value unchanged, use:

```php
Functions\expect('apply_filters')
    ->andReturnUsing(fn() => func_get_args()[1]);
```

Do **not** use `andReturnFirstArg()` — that returns the hook name, not the value.

### Pre-loaded functions cannot be mocked

Functions loaded via Composer's `files` autoload (like the `config()` helper) are defined before BrainMonkey initialises. Extract calls to these functions into protected methods and override them in tests.

### `wp_parse_args` is pre-stubbed

`YoastTestCase` pre-stubs `wp_parse_args` to behave like `array_merge($defaults, $args)` — no need to mock it yourself.
