# Context Managers

Context managers enrich the global `Timber::context()` with shared data that's available in every template. They're the quartermaster's store — making sure every template has the supplies it needs before setting sail.

## How They Work

Context managers implement the `ContextManagerInterface` and are registered in `config/context-managers.php`. During boot, the `TimberServiceProvider` instantiates each one and hooks them into the `timber/context` filter.

Every time `Timber::context()` is called (typically once per request, in a controller's constructor), each registered context manager has a chance to add its data.

### The Interface

{% code title="src/ContextManagers/ContextManagerInterface.php" %}
```php
namespace PressGang\ContextManagers;

interface ContextManagerInterface {
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function add_to_context(array $context): array;
}
```
{% endcode %}

## Built-in Context Managers

PressGang ships with five context managers out of the box:

### SiteContextManager

Adds the Timber `Site` object and a cache-busted stylesheet URL to the context.

**Context keys:** `site`, `site.stylesheet`

{% code title="views/layout.twig" %}
```twig
<link rel="stylesheet" href="{{ site.stylesheet }}">
<h1>{{ site.name }}</h1>
```
{% endcode %}

The stylesheet URL is filterable via `pressgang_stylesheet`.

### MenuContextManager

Adds all registered WordPress navigation menus as Timber `Menu` objects, keyed by location.

**Context keys:** `menu_{location}` (e.g. `menu_primary`, `menu_footer`)

{% code title="views/partials/nav.twig" %}
```twig
{% for item in menu_primary.items %}
    <a href="{{ item.link }}">{{ item.title }}</a>
{% endfor %}
```
{% endcode %}

Each menu is filterable via `pressgang_context_menu_{location}`.

### ThemeModsContextManager

Adds all WordPress Customizer theme modifications to the `theme` object.

**Context keys:** properties on `theme` (e.g. `theme.header_image`, `theme.custom_logo`)

{% code title="views/partials/header.twig" %}
```twig
{% if theme.custom_logo %}
    <img src="{{ theme.custom_logo }}" alt="{{ site.name }}">
{% endif %}
```
{% endcode %}

Each value is filterable via `pressgang_theme_mod_{key}`.

### AcfOptionsContextManager

Adds ACF (Advanced Custom Fields) options page fields to the context, converting values to Timber objects where appropriate. Results are cached via `wp_cache`.

**Context key:** `options`

{% code title="views/partials/footer.twig" %}
```twig
{{ options.company_name }}
{{ options.logo.src }}
```
{% endcode %}

{% hint style="info" %}
This manager only runs when ACF is active and `config/acf-options.php` is configured.
{% endhint %}

### WooCommerceContextManager

Adds WooCommerce-specific data to the context when WooCommerce is active.

## Creating a Custom Context Manager

{% stepper %}
{% step %}
### Create the class

{% code title="src/ContextManagers/SocialLinksContextManager.php" lineNumbers="true" %}
```php
namespace MyTheme\ContextManagers;

use PressGang\ContextManagers\ContextManagerInterface;

class SocialLinksContextManager implements ContextManagerInterface {

    public function add_to_context(array $context): array {
        $context['social_links'] = [
            'twitter'  => get_option('social_twitter'),
            'facebook' => get_option('social_facebook'),
            'instagram' => get_option('social_instagram'),
        ];

        return $context;
    }
}
```
{% endcode %}
{% endstep %}

{% step %}
### Register in config

Add it to your child theme's `config/context-managers.php`:

{% code title="config/context-managers.php" %}
```php
return [
    \PressGang\ContextManagers\SiteContextManager::class,
    \PressGang\ContextManagers\MenuContextManager::class,
    \PressGang\ContextManagers\ThemeModsContextManager::class,
    \PressGang\ContextManagers\AcfOptionsContextManager::class,
    \MyTheme\ContextManagers\SocialLinksContextManager::class,
];
```
{% endcode %}
{% endstep %}

{% step %}
### Use in Twig

{% code title="views/partials/social.twig" %}
```twig
<a href="{{ social_links.twitter }}">Twitter</a>
```
{% endcode %}
{% endstep %}
{% endstepper %}

## Important Guidelines

{% hint style="danger" %}
Context managers run on **every request** — frontend, admin, AJAX, and CLI. Keep them lightweight!
{% endhint %}

* **Cache non-trivial queries.** If your context manager fetches data from the database, wrap it in `wp_cache_get()`/`wp_cache_set()`.
* **Only add data needed across many templates.** Data specific to a single page should live in the controller, not a context manager.
* **Keep it side-effect free.** Context managers must not write to the database, send emails, or perform remote requests.
* **Don't overwrite built-in Timber context keys** like `site`, `request`, or `user`.
