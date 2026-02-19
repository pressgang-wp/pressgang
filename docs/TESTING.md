# Testing

PressGang ships with a unit test suite so you can verify framework behaviour and safely refactor without a running WordPress installation.

## Stack

- **PHPUnit 9.6** — test runner
- **yoast/wp-test-utils ^1.2** — provides BrainMonkey integration and pre-stubbed WordPress functions (matches Timber 2's own test stack)
- **BrainMonkey** — mocks WordPress functions (`add_action`, `apply_filters`, `wp_cache_get`, etc.) in pure PHP

No WordPress database, no web server, no Docker required.

## Running Tests

{% code title="Terminal" %}
```bash
composer test            # alias for test:unit
composer test:unit       # run the full unit suite
vendor/bin/phpunit --filter ConfigTest           # run a single test class
vendor/bin/phpunit --filter loads_and_caches     # run a single test by name
vendor/bin/phpunit --list-tests                  # list all discovered tests
```
{% endcode %}

## Directory Structure

Tests mirror the `src/` layout under `tests/Unit/`:

{% code title="tests/" %}
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
{% endcode %}

## Writing a New Test

{% stepper %}
{% step %}
### Create the test class

Place it under `tests/Unit/` mirroring the `src/` path. For example, a test for `src/Configuration/Sidebars.php` goes in `tests/Unit/Configuration/SidebarsTest.php`.
{% endstep %}

{% step %}
### Extend the base TestCase

{% code title="tests/Unit/Configuration/SidebarsTest.php" %}
```php
namespace PressGang\Tests\Unit\Configuration;

use PressGang\Tests\Unit\TestCase;

class SidebarsTest extends TestCase {
    // ...
}
```
{% endcode %}

The base `TestCase` extends `Yoast\WPTestUtils\BrainMonkey\YoastTestCase`, which handles BrainMonkey setup and teardown automatically. It also provides:

- `resetSingletonInstances()` — clears `ConfigurationSingleton` state between tests
- `setPostData()` / `clearPostData()` — helpers for testing form validators
{% endstep %}

{% step %}
### Mock WordPress functions with BrainMonkey

{% code title="Example test method" %}
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
{% endcode %}
{% endstep %}

{% step %}
### Reset singletons when needed

Any test that touches a `ConfigurationSingleton` subclass should reset state:

{% code title="setUp method" %}
```php
public function set_up(): void {
    parent::set_up();
    $this->resetSingletonInstances();
}
```
{% endcode %}
{% endstep %}
{% endstepper %}

## Testing Context Managers

Context managers depend on static calls (`Timber::get_menu()`, `new Site()`) and global helpers (`config()`) that cannot be mocked directly with BrainMonkey. PressGang uses the **protected method pattern** — static calls are wrapped in protected methods that tests override via anonymous subclasses:

{% tabs %}
{% tab title="Production class" %}
{% code title="src/ContextManagers/MenuContextManager.php" %}
```php
class MenuContextManager implements ContextManagerInterface {
    protected function get_menu(string $location): ?object {
        return Timber::get_menu($location);
    }
}
```
{% endcode %}
{% endtab %}

{% tab title="Test override" %}
{% code title="tests/Unit/ContextManagers/MenuContextManagerTest.php" %}
```php
private function makeManager(): MenuContextManager {
    return new class(['primary' => $menuStub]) extends MenuContextManager {
        public function __construct(private readonly array $menuMap) {}
        protected function get_menu(string $location): ?object {
            return $this->menuMap[$location] ?? null;
        }
    };
}
```
{% endcode %}
{% endtab %}
{% endtabs %}

This avoids `@runTestsInSeparateProcesses` (which is 5-10x slower) and keeps tests fast and deterministic.

## Tips and Gotchas

### BrainMonkey `apply_filters` signature

{% hint style="warning" %}
`apply_filters` receives `($hook, $value, ...$extra)`. To pass through the value unchanged, use the pattern below.
{% endhint %}

{% code title="Correct approach" %}
```php
Functions\expect('apply_filters')
    ->andReturnUsing(fn() => func_get_args()[1]);
```
{% endcode %}

{% hint style="danger" %}
Do **not** use `andReturnFirstArg()` — that returns the hook name, not the value.
{% endhint %}

### Pre-loaded functions cannot be mocked

Functions loaded via Composer's `files` autoload (like the `config()` helper) are defined before BrainMonkey initialises. Extract calls to these functions into protected methods and override them in tests.

### `wp_parse_args` is pre-stubbed

{% hint style="info" %}
`YoastTestCase` pre-stubs `wp_parse_args` to behave like `array_merge($defaults, $args)` — no need to mock it yourself.
{% endhint %}
