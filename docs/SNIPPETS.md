# Snippets

Snippets are PressGang's answer to the WordPress `functions.php` junk drawer. Instead of dumping unrelated functionality into a single file — analytics scripts next to image size definitions next to admin tweaks — each concern gets its own class, with its own configuration, that can be enabled or disabled with a single line.

Think of snippets as your ship's provisions — pre-packaged, self-contained, ready to be loaded aboard any theme.

## Why Snippets Instead of `functions.php`

In a typical WordPress theme, `functions.php` grows into a sprawling file that mixes unrelated concerns: analytics tracking, custom image sizes, admin tweaks, WooCommerce overrides, Customizer settings. This creates problems:

- **Hard to find things.** Where's the code that disables emojis? Somewhere in 800 lines.
- **Hard to reuse.** Want the same analytics setup on another site? Copy-paste and hope you got everything.
- **Hard to disable.** Commenting out blocks of code is error-prone and messy.
- **Hard to share.** Distributing a `functions.php` snippet via Composer isn't practical.

Snippets solve all of these:

| `functions.php` approach | Snippet approach |
|---|---|
| One file, many concerns | One class per concern |
| Enable/disable by commenting code | Enable/disable by adding/removing a config line |
| Copy-paste between projects | Install via Composer, share across all themes |
| Arguments buried in code | Configuration passed explicitly via `$args` |
| No standard structure | Every snippet implements the same interface |
| Grows without limit | Each snippet stays small and focused |

## How Snippets Work

### The Interface

Every snippet implements `SnippetInterface`, which requires exactly one thing — a constructor that accepts an array of arguments:

{% code title="src/Snippets/SnippetInterface.php" %}
```php
namespace PressGang\Snippets;

interface SnippetInterface {
    public function __construct(array $args);
}
```
{% endcode %}

The constructor is where the snippet registers its WordPress hooks. Once constructed, the snippet is fully operational — no additional calls needed.

### The Config File

Snippets are activated in your theme's `config/snippets.php`. Each entry maps a snippet class to its arguments:

{% code title="config/snippets.php" lineNumbers="true" %}
```php
return [
    // No configuration needed — pass an empty array
    'PressGang\\Snippets\\DisableEmojis' => [],

    // Configuration passed via the args array
    'PressGang\\Snippets\\ImageSizes' => [
        'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
        'hero'      => ['width' => 1920, 'height' => 600, 'crop' => true],
        'hero'      => false,  // Disable a size
    ],

    // Customizer-based snippets configure themselves via the WP Customizer
    'PressGang\\Snippets\\GoogleAnalytics' => [],
];
```
{% endcode %}

To disable a snippet, remove or comment out its line. To reconfigure one, change its `$args` array. No code changes needed.

### Namespace Resolution

PressGang resolves snippet class names in this order:

1. **Fully qualified** — if the name starts with `PressGang\` or your child theme namespace, it's used directly.
2. **Child theme first** — `YourTheme\Snippets\SnippetName` is checked, allowing you to override a library snippet with your own version.
3. **Parent fallback** — `PressGang\Snippets\SnippetName` is used if no child theme override exists.

This means you can reference snippets by short name when they live in a standard namespace:

{% code title="config/snippets.php" %}
```php
return [
    // These are equivalent:
    'PressGang\\Snippets\\DisableEmojis' => [],
    'DisableEmojis' => [],

    // Subfolders work too:
    'WooCommerce\\ProductColorSwatch' => [],
];
```
{% endcode %}

### Template Paths

PressGang automatically adds the `pressgang-snippets` vendor views directory to Timber's template lookup paths. Twig templates bundled with snippet packages are available to your theme without any manual path configuration.

## Anatomy of a Snippet

Here's what a typical snippet looks like — this one adds Google Analytics tracking via the WordPress Customizer:

{% code title="src/Snippets/GoogleAnalytics.php" lineNumbers="true" %}
```php
namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Injects a Google Analytics (gtag.js) tracking script into wp_head. The tracking
 * ID is managed via the WordPress Customizer under a "Google" section. An optional
 * toggle controls whether logged-in users are tracked.
 *
 * No $args configuration needed — the tracking ID is entered via the Customizer.
 */
class GoogleAnalytics implements SnippetInterface {

    public function __construct(array $args) {
        \add_action('customize_register', [$this, 'add_to_customizer']);
        \add_action('wp_head', [$this, 'script']);
    }

    public function add_to_customizer(\WP_Customize_Manager $wp_customize): void {
        // Add Customizer section, setting, and control...
    }

    public function script(): void {
        if ($google_analytics_id = \get_theme_mod('google-analytics-id')) {
            Timber::render('snippets/google-analytics.twig', [
                'google_analytics_id' => $google_analytics_id,
            ]);
        }
    }
}
```
{% endcode %}

Key things to notice:

- **Constructor registers hooks** — `customize_register` for the Customizer UI, `wp_head` for the script output.
- **No work in the constructor itself** — it only wires up hooks for WordPress to call later.
- **Renders via Timber** — output goes through a Twig template, not inline `echo` statements.
- **Guards its output** — checks that a tracking ID exists before rendering anything.

## Writing Your Own Snippets

Child themes will often need site-specific snippets that don't belong in the shared library. This is expected and encouraged — it's far better to write a snippet class than to add code to `functions.php`.

{% stepper %}
{% step %}
### Create the class

Place it in your child theme's `src/Snippets/` directory:

{% code title="src/Snippets/AdminBarQuoteButton.php" lineNumbers="true" %}
```php
namespace YourTheme\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds a "Request a Quote" button to the admin bar on product pages.
 *
 * The button links to a configurable URL passed via $args. Only appears for
 * users with the 'edit_posts' capability.
 */
class AdminBarQuoteButton implements SnippetInterface {

    private string $url;

    public function __construct(array $args) {
        $this->url = $args['url'] ?? '/contact';
        \add_action('admin_bar_menu', [$this, 'add_button'], 100);
    }

    public function add_button(\WP_Admin_Bar $admin_bar): void {
        if (!\current_user_can('edit_posts') || !\is_singular('product')) {
            return;
        }

        $admin_bar->add_node([
            'id'    => 'request-quote',
            'title' => \__('Request a Quote', THEMENAME),
            'href'  => \esc_url($this->url),
        ]);
    }
}
```
{% endcode %}
{% endstep %}

{% step %}
### Register in config

{% code title="config/snippets.php" %}
```php
return [
    'AdminBarQuoteButton' => ['url' => '/request-quote'],
];
```
{% endcode %}

Because your child theme namespace is checked first, you only need the short class name.
{% endstep %}

{% step %}
### Add a Twig template (if needed)

If your snippet renders output, place the template in your child theme's `views/snippets/` directory. Render it via `Timber::render('snippets/your-template.twig', $context)`.
{% endstep %}
{% endstepper %}

## Common Snippet Patterns

<details>
<summary><strong>Customizer + Render</strong></summary>

Adds a setting to the WordPress Customizer and renders output based on that setting. Used for third-party scripts, tracking pixels, and theme options that need a simple admin UI.

{% code title="Pattern" %}
```php
public function __construct(array $args) {
    \add_action('customize_register', [$this, 'add_to_customizer']);
    \add_action('wp_head', [$this, 'render']);
}
```
{% endcode %}

**Examples:** `GoogleAnalytics`, `GoogleTagManager`, `FacebookPixel`, `Hotjar`, `GoogleRecaptcha`

</details>

<details>
<summary><strong>Hook Filtering</strong></summary>

Modifies WordPress behaviour via actions and filters. No UI, no templates — just behavioural changes.

{% code title="Pattern" %}
```php
public function __construct(array $args) {
    $this->exclude = $args['exclude'] ?? [];
    \add_filter('pre_get_posts', [$this, 'modify_query']);
}
```
{% endcode %}

**Examples:** `DisableEmojis`, `BigImageScaling`, `SearchExcludePostTypes`, `RemovePosts`

</details>

<details>
<summary><strong>Config-Driven Registration</strong></summary>

Receives structured `$args` and registers WordPress resources. The args array shape mirrors WordPress API conventions.

{% code title="Pattern" %}
```php
public function __construct(array $args) {
    $this->args = $args;
    \add_action('init', [$this, 'setup_image_sizes']);
}
```
{% endcode %}

**Examples:** `ImageSizes`, `Permalinks`, `AddQueryVars`

</details>

<details>
<summary><strong>Admin Features</strong></summary>

Adds functionality to the WordPress admin — row actions, admin notices, editor customisation. Always includes capability checks and nonce verification.

**Examples:** `DuplicatePost`, `AdminLogo`, `TinyMceBlockFormats`

</details>

<details>
<summary><strong>Twig Function Registration</strong></summary>

Registers a callable function into the Twig environment, making it available in templates as `{{ function_name() }}`.

{% code title="Pattern" %}
```php
public function __construct(array $args) {
    \add_filter('timber/twig', [$this, 'add_to_twig']);
}

public function add_to_twig(Environment $twig): Environment {
    $twig->addFunction(new TwigFunction('breadcrumb', [$this, 'render']));
    return $twig;
}
```
{% endcode %}

**Examples:** `Breadcrumb`

</details>

## Guidelines

{% hint style="danger" %}
Snippets are constructed during theme setup and their hooks fire on every request. Keep constructors lightweight — register hooks only, don't do real work.
{% endhint %}

- **One concern per snippet.** If you're tempted to add unrelated functionality, create a second snippet.
- **Accept `array $args`** and document what keys are supported. Provide sensible defaults.
- **Guard for context.** Don't assume you're on the frontend — snippets may fire on admin, AJAX, or CLI requests. Check `\is_admin()`, `\is_singular()`, etc. where appropriate.
- **Guard for dependencies.** If a snippet depends on ACF or WooCommerce, check `function_exists()` or `class_exists()` before calling their APIs.
- **Escape output.** Use Twig auto-escaping for templates. Use `\esc_html()`, `\esc_attr()`, `\esc_url()` for PHP output.
- **Sanitise input.** Always apply `sanitize_text_field()`, `\absint()`, etc. to values from `$_GET`/`$_POST` or Customizer settings.
- **Use fully-qualified function calls.** Write `\add_action()`, not `add_action()`, in namespaced code.

## PressGang Snippets Library

A curated collection of 45+ ready-to-use snippets is available as a separate Composer package:

{% code title="Terminal" %}
```bash
composer require pressgang-wp/pressgang-snippets
```
{% endcode %}

This includes snippets for Google Analytics, Tag Manager, Facebook Pixel, emoji removal, image sizes, breadcrumbs, Open Graph tags, JSON-LD schemas, WooCommerce tweaks, and more. See [pressgang-wp/pressgang-snippets](https://github.com/pressgang-wp/pressgang-snippets) for the full list.
