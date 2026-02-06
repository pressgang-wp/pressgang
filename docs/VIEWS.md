# Views

In PressGang, views are responsible for rendering the HTML output for the front-end of the website. Views are created using the [Twig](https://twig.symfony.com/) templating engine, provided by the [Timber library](https://timber.github.io/docs/v2/). This approach separates the presentation logic from the business logic, promoting cleaner and more maintainable code.

Twig templates are the sails of your PressGang ship — they catch the context data prepared by your controllers and turn it into beautiful, well-structured HTML.

## Twig Templates

Twig is a modern templating engine for PHP, offering a clean syntax that is easy to read and write. Timber integrates Twig into WordPress, allowing you to use Twig templates for rendering your theme views.

See:

* [Twig Documentation](https://twig.symfony.com/doc/3.x/)
* [Timber Documentation](https://timber.github.io/docs/v2/)
* [Timber Getting Started](https://timber.github.io/docs/v2/getting-started/introduction/)

### Using Timber via PressGang Controllers to Render Views

While Timber provides a simple way to render Twig templates with context data (see the [Timber introduction](https://timber.github.io/docs/v2/getting-started/introduction/#a-view)), PressGang takes this a step further by introducing Controllers to prepare context data in your WordPress templates.

In a typical PressGang setup, the `AbstractController` takes a `$template` argument in its constructor for the Twig template name, and attaches the base `Timber::context()` to the `$context` class property:

```php
use Timber\Timber;

abstract class AbstractController implements ControllerInterface {

    public function __construct(?string $template = null) {
        $this->template = $template;
        $this->context  = Timber::context();
    }

}
```

Each controller's `get_context()` method then enriches the context with page-specific data.

### Example: PostController

The `PostController` adds the current post to the context under both `'post'` and a post-type-specific key:

```php
protected function get_context(): array {
    $post = $this->get_post();

    $this->context['post']             = $post;
    $this->context[ $this->post_type ] = $post;

    return $this->context;
}
```

This means in your `single.twig` template, you can access the post as `{{ post }}` or by its type, e.g., `{{ event }}` for a custom post type called `event`.

### Example: PostsController

The `PostsController` adds posts, pagination, and a page title for archive pages:

```php
protected function get_context(): array {
    $this->context['page_title']  = $this->get_page_title();
    $this->context['pagination']  = $this->get_pagination();
    $this->context['posts']       = $this->get_posts();

    return $this->context;
}
```

## Escaping

Twig's auto-escaping is enabled by default. This means `{{ value }}` is automatically HTML-escaped — you do not need to call `esc_html()` or similar WordPress functions inside Twig.

* **Attributes:** `{{ value|e('html_attr') }}`
* **URLs:** `{{ value|e('url') }}`
* **Raw HTML:** `{{ value|raw }}` — only when the value has been sanitised in PHP and is explicitly intended to contain HTML.

{% hint style="warning" %}
Sanitise input in PHP before passing it to the context. Let Twig handle the escaping on output. Don't mix the two!
{% endhint %}

## Translations (i18n)

PressGang uses a single text domain constant, `THEMENAME`, which is available as a Twig global:

```twig
{{ __('Read more', THEMENAME) }}
{{ __('View %s', THEMENAME)|format(post.title) }}
```

Do not concatenate translated strings — use format placeholders instead.

## Views Directory

The `views` folder in your child theme is by default the home of your Twig templates.

Timber will look for templates in the child theme first, then falls back to the parent theme (just like WordPress itself). See [Timber template locations](https://timber.github.io/docs/v2/guides/template-locations/).

The PressGang parent theme organises its views into the following subdirectories:

```
views/
  layouts/     — Base page layouts (header, footer, body structure)
  macros/      — Reusable Twig macros
  partials/    — Reusable template fragments
  scaffold/    — Structural scaffolding templates
  shared/      — Shared components used across templates
```

You can follow this same structure in your child theme, or organise views however suits your project. Any file in your child theme's `views/` directory will take precedence over the parent theme's version.
