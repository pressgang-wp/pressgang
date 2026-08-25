---
description: >-
  PHPUnit unit tests and PHPStan static analysis for PressGang's own PHP
  framework code — no WordPress install required — plus where to go for full
  theme end-to-end testing.
---

# 🧪 Testing

PressGang ships with a unit test suite so you can verify framework behaviour and safely refactor without a running WordPress installation.

## 🧰 Stack

* **[PHPUnit](https://phpunit.de/) 9.6** — test runner
* **[yoast/wp-test-utils](https://github.com/Yoast/wp-test-utils) ^1.2** — provides BrainMonkey integration and pre-stubbed WordPress functions (matches Timber 2's own test stack)
* **[BrainMonkey](https://brain-wp.github.io/BrainMonkey/)** — mocks WordPress functions (`add_action`, `apply_filters`, `wp_cache_get`, etc.) in pure PHP

No WordPress database, no web server, no Docker required.

## ▶️ Running Tests

{% code title="Terminal" %}
```bash
composer test            # alias for test:unit
composer test:unit       # run the full unit suite
composer test:compat     # run the unit suite with strict PHP error reporting
composer check           # local convenience: test:compat + phpstan
vendor/bin/phpunit --filter ConfigTest           # run a single test class
vendor/bin/phpunit --filter loads_and_caches     # run a single test by name
vendor/bin/phpunit --list-tests                  # list all discovered tests
```
{% endcode %}

## 🗂️ Directory Structure

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

## ✍️ Writing a New Test

{% stepper %}
{% step %}
#### Create the test class

Place it under `tests/Unit/` mirroring the `src/` path. For example, a test for `src/Configuration/Sidebars.php` goes in `tests/Unit/Configuration/SidebarsTest.php`.
{% endstep %}

{% step %}
#### Extend the base TestCase

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

* `resetSingletonInstances()` — clears `ConfigurationSingleton` state between tests
* `setPostData()` / `clearPostData()` — helpers for testing form validators
{% endstep %}

{% step %}
#### Mock WordPress functions with BrainMonkey

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
#### Reset singletons when needed

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

## 🔧 Testing Context Managers

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

## 💡 Tips and Gotchas

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

## 🔬 Static Analysis

PressGang also runs [PHPStan](https://phpstan.org/) at level 8, so type errors and nullability bugs are caught before a test even needs to exist for them.

{% code title="Terminal" %}
```bash
composer phpstan
```
{% endcode %}

* **`szepeviktor/phpstan-wordpress`** — WordPress core function/class stubs
* **`php-stubs/woocommerce-stubs`** and **`php-stubs/acf-pro-stubs`** — stubs for the two plugin APIs PressGang integrates with most deeply

`composer check` is intentionally narrow: it runs `test:compat` and `phpstan`,
and nothing else. Keep CI jobs split into their existing separate steps so the
Actions UI still shows whether tests, compatibility, static analysis, or
browser/runtime checks failed.

## 🧭 Theme tooling convention

Child themes should use the same shape:

* PHPStan level 8 with a local `phpstan.neon.dist`.
* `composer phpstan` for static analysis.
* `composer test:compat` for the strict PHP compatibility/unit pass when the theme has tests.
* `composer check` as a local convenience alias for `test:compat` + `phpstan` only.
* Project stubs for vendor/runtime type mismatches when they model reality better than an ignore.
* Explicit, documented ignores only when a source fix, docblock improvement, or stub would be worse.

Agents should run `composer check` when it exists. If it does not, run the
project's documented test and static-analysis commands separately. Treat
PHPStan findings as guidance for improving source, PHPDoc, stubs, or config;
do not add baselines or broad ignores unless a maintainer explicitly asks.

{% hint style="info" %}
There is no PHPStan baseline in this repo. If `composer phpstan` fails on
something you wrote, fix the type rather than baselining it.
{% endhint %}

A couple of ignored rules in `phpstan.neon.dist` are architectural, not suppressed bugs — see **Known Exceptions** in [AGENTS.md](https://github.com/pressgang-wp/pressgang/blob/main/AGENTS.md) for why `trait.unused` and `CustomMenuItems.php`'s dynamic `WP_Post` properties are ignored.

## 🧱 Tool boundaries

Static analysis, runtime introspection, and end-to-end verification are separate
signals:

* PHPStan catches static type, nullability, and convention drift in PHP code.
* [Capstan](CAPSTAN.md) provides runtime introspection with `wp capstan resolve`,
  `wp capstan context`, `wp capstan config dump`, and `wp capstan doctor`.
* [Shakedown](SHAKEDOWN.md) uses Capstan's `wp capstan matrix --resolve` oracle
  to assert route/controller/runtime behaviour in CI.

Do not design new route, controller, context, or config-dump validation here:
those surfaces already exist in Capstan and Shakedown.

There is no shared `pressgang/phpstan` package yet. Track repeated extraction
candidates — nav-menu `WP_Post` dynamic properties, WooCommerce cart lifecycle
stubs, WooCommerce/ACF stub bundling, and `phpstan-bootstrap.php` constants —
but extract only after a second PressGang repo independently adopts PHPStan
level 8 and hits the same needs. One consumer is not a package.

## 🚢 End-to-end testing

Unit tests cover the framework's PHP in isolation. For testing an actual **theme** — every route rendered in a real browser, accessibility, visual regression, derived fixtures — see [Shakedown](SHAKEDOWN.md), the fleet's e2e harness. It needs zero authored tests to start: the suite is derived from your theme's config.
