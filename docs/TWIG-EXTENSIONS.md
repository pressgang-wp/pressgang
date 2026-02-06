# Twig Extensions

Twig extensions let you add custom functions, filters, and globals to the Twig templating environment. They're the rigging of your PressGang ship — connecting the PHP engine room to the Twig deck where templates do their work.

## How They Work

All Twig extensions are managed through extension manager classes that implement the `TwigExtensionManagerInterface`. These managers are registered in `config/twig-extensions.php` and wired up during boot by the `TimberServiceProvider`.

### The Interface

```php
namespace PressGang\TwigExtensions;

use Twig\Environment;

interface TwigExtensionManagerInterface {
    public function add_twig_functions(Environment $twig): void;
    public function add_twig_filters(Environment $twig): void;
    public function add_twig_globals(Environment $twig): void;
}
```

Each method receives the Twig `Environment` and can register any number of functions, filters, or globals.

### Convenience Traits

If your extension only needs to implement one or two of the three methods, PressGang provides no-op traits to keep your code clean:

* `HasNoFunctions` — provides an empty `add_twig_functions()`.
* `HasNoFilters` — provides an empty `add_twig_filters()`.
* `HasNoGlobals` — provides an empty `add_twig_globals()`.

## Built-in Extension Managers

### GeneralExtensionManager

Registers general-purpose functions and globals:

**Functions:**
* `get_search_query()` — returns the current search query string.
* `get_option(name)` — retrieves a WordPress option.
* `get_theme_mod(name)` — retrieves a theme modification value.

**Globals:**
* `THEMENAME` — the text domain constant, for use in translation calls.

```twig
<form action="/">
    <input type="search" value="{{ get_search_query() }}">
</form>

<p>{{ __('Welcome aboard!', THEMENAME) }}</p>
```

### MetaDescriptionExtensionManager

**Functions:**
* `meta_description()` — generates an SEO-friendly meta description for the current page, via the `MetaDescriptionService`.

```twig
<meta name="description" content="{{ meta_description() }}">
```

See [SEO](SEO.md) for details on the meta description fallback chain.

### SinglePostExtensionManager

Only active on single post pages. Requires the post to be mapped to `PressGang\Post` via the `timber-class-map` config.

**Functions:**
* `get_latest_posts(count)` — fetches the latest posts (excluding the current one).
* `get_related_posts(count)` — fetches posts related to the current one by shared taxonomy terms.

```twig
{% for post in get_related_posts(3) %}
    <a href="{{ post.link }}">{{ post.title }}</a>
{% endfor %}
```

### WidgetExtensionManager

Registers a Twig function for rendering WordPress widgets in templates.

### WooCommerceExtensionManager

Registers WooCommerce-specific Twig functions when WooCommerce is active.

## Creating a Custom Extension Manager

### 1. Create the class

```php
namespace MyTheme\TwigExtensions;

use PressGang\TwigExtensions\HasNoFilters;
use PressGang\TwigExtensions\HasNoGlobals;
use PressGang\TwigExtensions\TwigExtensionManagerInterface;
use Twig\Environment;
use Twig\TwigFunction;

class SocialExtensionManager implements TwigExtensionManagerInterface {

    use HasNoFilters;
    use HasNoGlobals;

    public function add_twig_functions(Environment $twig): void {
        $twig->addFunction(new TwigFunction('share_url', function (string $platform, string $url): string {
            return match ($platform) {
                'twitter'  => "https://twitter.com/intent/tweet?url=" . urlencode($url),
                'facebook' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url),
                default    => $url,
            };
        }));
    }
}
```

### 2. Register in config

Add to your child theme's `config/twig-extensions.php`:

```php
return [
    \PressGang\TwigExtensions\GeneralExtensionManager::class,
    \PressGang\TwigExtensions\MetaDescriptionExtensionManager::class,
    \PressGang\TwigExtensions\SinglePostExtensionManager::class,
    \PressGang\TwigExtensions\WidgetExtensionManager::class,
    \MyTheme\TwigExtensions\SocialExtensionManager::class,
];
```

### 3. Use in Twig

```twig
<a href="{{ share_url('twitter', post.link) }}">Share on Twitter</a>
```

## Rules for Twig Functions

{% hint style="warning" %}
Twig is for presentation only. Keep your Twig functions pure and side-effect free!
{% endhint %}

* **No database queries** — if you need data, provide it via a controller or context manager.
* **No writes** — no `update_option()`, `wp_insert_post()`, or similar.
* **No remote requests** — no `wp_remote_get()` or API calls.
* **Deterministic** — same inputs should always produce the same outputs.
* **Pure** — no side effects, no mutation of global state.

The one documented exception is `WooCommerceExtensionManager::timber_set_product()`, which sets `global $product` as required by WooCommerce's template system.
