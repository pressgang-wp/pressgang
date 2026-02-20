# Service Providers

Service providers are PressGang's hook for bootstrapping services after the framework has initialised. They run after Timber and the Loader are ready, making them the right place for wiring up filters, registering integrations, or initialising third-party libraries.

PressGang ships with one service provider by default — `TimberServiceProvider` — which registers context managers, Twig extensions, Twig environment options, and snippet template paths. But you can add your own alongside it.

## How They Work

```mermaid
graph LR
    A["config/service-providers.php"] --> B["pressgang_service_providers filter"]
    B --> C["PressGang instantiates each class"]
    C --> D["boot() called on each provider"]
```

{% stepper %}
{% step %}
### 1. Config declares providers

Class strings are listed in `config/service-providers.php`. Child theme config **replaces** the parent file, so always include `TimberServiceProvider` unless you're intentionally removing it.
{% endstep %}

{% step %}
### 2. Filter allows modification

The list is passed through the `pressgang_service_providers` filter, letting plugins or mu-plugins add or remove providers.
{% endstep %}

{% step %}
### 3. PressGang boots each one

Each class string is validated: it must be a loadable class implementing `ServiceProviderInterface`. Invalid entries are skipped silently — no fatal errors from a bad config line.
{% endstep %}
{% endstepper %}

## The Interface

Every service provider implements `ServiceProviderInterface`, which requires exactly one method:

{% code title="src/ServiceProviders/ServiceProviderInterface.php" %}
```php
namespace PressGang\ServiceProviders;

interface ServiceProviderInterface {
    public function boot(): void;
}
```
{% endcode %}

{% hint style="info" %}
Providers are instantiated with **no constructor arguments**. If your provider needs configuration, read it from `Config::get()` or WordPress options inside `boot()`.
{% endhint %}

## Default Configuration

{% code title="config/service-providers.php" lineNumbers="true" %}
```php
return [
    \PressGang\ServiceProviders\TimberServiceProvider::class,
];
```
{% endcode %}

`TimberServiceProvider` wires up:

| Concern | Config source | Hook |
|---|---|---|
| Context managers | `config/context-managers.php` | `timber/context` |
| Twig extensions | `config/twig-extensions.php` | `timber/twig` |
| Twig environment options | `config/timber.php` | `timber/twig/environment/options` |
| Snippet template paths | vendor directory | `timber/locations` |

See [Context Managers](CONTEXT-MANAGERS.md) and [Twig Extensions](TWIG-EXTENSIONS.md) for details on each.

## Writing a Custom Service Provider

{% stepper %}
{% step %}
### Create the class

{% code title="src/ServiceProviders/SearchServiceProvider.php" lineNumbers="true" %}
```php
namespace MyTheme\ServiceProviders;

use PressGang\ServiceProviders\ServiceProviderInterface;

/**
 * Customises the main search query to exclude specific post types
 * and boost exact title matches.
 */
class SearchServiceProvider implements ServiceProviderInterface {

    public function boot(): void {
        \add_action('pre_get_posts', [$this, 'customise_search']);
    }

    public function customise_search(\WP_Query $query): void {
        if (! $query->is_main_query() || ! $query->is_search() || \is_admin()) {
            return;
        }

        $query->set('post_type', ['post', 'page', 'event']);
    }
}
```
{% endcode %}
{% endstep %}

{% step %}
### Register in config

{% code title="config/service-providers.php" lineNumbers="true" %}
```php
return [
    \PressGang\ServiceProviders\TimberServiceProvider::class,
    \MyTheme\ServiceProviders\SearchServiceProvider::class,
];
```
{% endcode %}

{% hint style="warning" %}
A child theme's `config/service-providers.php` **replaces** the parent file entirely. Always include `TimberServiceProvider` unless you are intentionally removing PressGang's default Timber integration.
{% endhint %}
{% endstep %}
{% endstepper %}

## Adding a Provider via Filter

Plugins and mu-plugins can add providers without touching config files:

{% code title="Plugin or mu-plugin" lineNumbers="true" %}
```php
add_filter('pressgang_service_providers', function (array $providers): array {
    $providers[] = \MyPlugin\ServiceProviders\AnalyticsServiceProvider::class;
    return $providers;
});
```
{% endcode %}

## Guidelines

{% hint style="danger" %}
Service providers boot on **every request**. Keep `boot()` lightweight — register hooks and filters only, don't do real work.
{% endhint %}

- **Register hooks in `boot()`, execute work in callbacks.** The `boot()` method should only call `add_action()` / `add_filter()` — not perform queries, remote requests, or heavy computation.
- **One concern per provider.** If your provider handles both search customisation and email configuration, split it into two providers.
- **Guard for dependencies.** If your provider depends on a plugin (ACF, WooCommerce, etc.), check `class_exists()` or `function_exists()` before registering hooks.
- **No constructor arguments.** PressGang instantiates providers with `new $class()`. Use `Config::get()` or WordPress options for configuration.

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `pressgang_service_providers` | filter | Modify the list of service provider class strings before boot |
