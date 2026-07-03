# Controllers

## Overview

In PressGang, controllers manage the display logic for different types of pages and templates, often reflecting the WordPress template hierarchy, e.g., `PageController`. Conventionally, they use singular names (e.g., `PostController`) to represent single pages, and plural names (e.g., `PostsController`) to represent archive pages. Essentially, these classes build the Timber context that gets passed to the views (Twig templates).

Controllers are your first mates — they prepare everything the view needs, then hand it off cleanly.

### AbstractController Base Class

The `AbstractController` class provides common functionalities for all controllers, including context management and template rendering.

### Key Methods

* `__construct(string|null $template = null)`: Initializes the controller with the specified Twig template and attaches the base `Timber::context()`.
* `get_context()`: Builds and returns the context array for the template.
* `render()`: Renders the Twig template with the current context, applying PressGang filters and actions.

### Available Controllers

PressGang ships with controllers for all the common WordPress template types:

| Controller | Template | Purpose |
|---|---|---|
| `PageController` | `page.twig` | Standard WordPress pages |
| `PostController` | `single.twig` | Single post views (auto-detects post type) |
| `PostsController` | `archive.twig` | Archive listings, categories, search results |
| `SearchController` | `search.twig` | Search results (extends PostsController) |
| `AuthorController` | `author.twig` | Author archive pages |
| `TaxonomyController` | varies | Taxonomy archive pages |
| `CommentsController` | `comments.twig` | Comments template |
| `NotFoundController` | `404.twig` | 404 error page |

WooCommerce controllers are also provided under `PressGang\Controllers\WooCommerce\`.

## Example: `PageController`

{% code title="src/Controllers/PageController.php" lineNumbers="true" %}
```php
namespace PressGang\Controllers;

use Timber\Post;
use Timber\Timber;

class PageController extends AbstractController {
    protected Post $post;

    public function __construct(string|null $template = 'page.twig') {
        parent::__construct($template);
    }

    protected function get_post(): Post {
        if (empty($this->post)) {
            $this->post = Timber::get_post();
        }
        return $this->post;
    }

    protected function get_context(): array {
        $post = $this->get_post();
        $this->context['page'] = $post;
        $this->context['post'] = $post;
        return $this->context;
    }
}
```
{% endcode %}

## Usage in Templates

Controllers are utilized in standard WordPress template files. PressGang maintains the familiar WordPress template hierarchy — you still create `page.php`, `single.php`, `archive.php`, etc. — but instead of writing queries and HTML, you delegate to a controller.

{% tabs %}
{% tab title="PressGang::render() (Recommended)" %}
The static `render()` method resolves the controller and template for you:

{% code title="page.php" %}
```php
use PressGang\Controllers\PageController;

PressGang\PressGang::render(controller: PageController::class, twig: 'front-page.twig');
```
{% endcode %}

You can also let PressGang infer the controller automatically from the template filename:

{% code title="page.php" %}
```php
PressGang\PressGang::render(template: 'page.php');
```
{% endcode %}
{% endtab %}

{% tab title="Direct Instantiation" %}
For more control, instantiate the controller directly:

{% code title="front-page.php" %}
```php
use PressGang\Controllers\PageController;

(new PageController('front-page.twig'))->render();
```
{% endcode %}
{% endtab %}
{% endtabs %}

{% hint style="info" %}
Tired of writing stub files at all? With [Template Routing](TEMPLATE-ROUTING.md) enabled, requests resolve to controllers by naming convention — most themes need no template PHP files.
{% endhint %}

## Context Getters: Declare the Template Contract

Wiring context keys one line at a time gets old fast. Instead, declare the keys your template needs and let each one populate from its matching getter:

{% code title="src/Controllers/FrontPageController.php" lineNumbers="true" %}
```php
namespace MyTheme\Controllers;

use PressGang\Controllers\PageController;

class FrontPageController extends PageController {

    /**
     * Template contract for front-page.twig: latest news and upcoming
     * events, each populated from its get_{key}() getter.
     *
     * @var array<int|string, string>
     */
    protected array $context_getters = [ 'news', 'events' ];

    protected function get_news(): array {
        return $this->news ??= /* ... query ... */;
    }

    protected function get_events(): array {
        return $this->events ??= /* ... query ... */;
    }
}
```
{% endcode %}

Each plain entry calls `get_{key}()`; use `'key' => 'method'` to point a key at a differently-named getter. The manifest is applied after `get_context()` and before the `pressgang_{controller}_context` filter, so both extension points still work — and with a manifest, most controllers don't need a `get_context()` override at all.

{% hint style="info" %}
This is the controller counterpart to the `HandlesDynamicGetters` trait on models: getters own the *fetching*, the manifest declares which of them form the *template contract*. Keep it a declared list — the framework deliberately never auto-publishes getters, or your internal helpers would silently become template API.
{% endhint %}

## Working with ACF Values

ACF relationship and post-object fields return raw `WP_Post` objects or IDs — but Twig wants Timber posts. Convert with the same mapper the ACF options context manager uses:

{% code title="Controller" lineNumbers="true" %}
```php
use PressGang\ACF\TimberMapper;

protected function get_related(): array {
    return TimberMapper::to_timber_posts( $this->get_post()->meta( 'related_items' ) );
}
```
{% endcode %}

It accepts the raw field value directly (empty and `false` values are fine) and returns a clean array of `Timber\Post` objects. Prefer this explicit conversion over enabling Timber's global `timber/meta/transform_value` filter, which silently changes the return type of *every* `meta()` call — and doesn't reach values inside flexible-content or repeater sub-fields anyway.

## Filters and Actions

The `render()` method fires several hooks, giving you fine-grained control over any controller's output:

- **`pressgang_{controller}_template`** — filter the Twig template path before rendering.
- **`pressgang_{controller}_context`** — filter the context array before it reaches Twig.
- **`pressgang_render_{controller}`** — action fired just before `Timber::render()`.

The `{controller}` placeholder is the snake_case version of the controller class name, e.g., `pressgang_page_controller_template`.

## Extending Controllers in Child Themes

To extend the functionality of a parent theme controller in a child theme, create a new controller class in the child theme that inherits from the parent controller.

{% code title="src/Controllers/ChildPageController.php" lineNumbers="true" %}
```php
namespace ChildTheme\Controllers;

use PressGang\Controllers\PageController;

class ChildPageController extends PageController {
    protected function get_context(): array {
        $context = parent::get_context();
        $context['custom_data'] = 'Additional data';
        return $context;
    }
}
```
{% endcode %}

Then use it in your child theme's template:

{% code title="child-theme/page.php" %}
```php
use ChildTheme\Controllers\ChildPageController;

PressGang\PressGang::render(controller: ChildPageController::class);
```
{% endcode %}

This setup allows the child theme to inherit and extend the logic defined in the parent theme controllers, promoting code reuse and maintainability.

### Note on MVC Abstraction

{% hint style="info" %}
While these controllers are named similarly to traditional MVC Controllers, they function more closely as **View Models**.
{% endhint %}

In classic MVC:

- **Model:** Handles data and business logic.
- **View:** Manages the display of information.
- **Controller:** Acts as an intermediary, handling user input, updating the Model, and refreshing the View.

In PressGang, the Controllers primarily prepare and manage context data for the View (Twig templates), aligning more with the View Model pattern. They focus on preparing data for the View without directly handling user input or business logic.

{% hint style="danger" %}
Controllers must **not** perform writes, remote requests, or access request globals like `$_GET` or `$_POST`.
{% endhint %}
