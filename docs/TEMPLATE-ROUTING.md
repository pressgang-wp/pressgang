# 🗺️ Template Routing

## Overview

Template routing lets requests find their controllers **by convention** — most themes need no template PHP files at all. No more three-line stub files whose only job is naming a controller and a Twig template: the filename already told us everything.

Think of it as the ship's watch rota: every request already knows its station.

Template routing is **opt-in**. Enable it by adding the service provider to your child theme's `config/service-providers.php`:

{% code title="config/service-providers.php" lineNumbers="true" %}
```php
return [
    // PressGang defaults — always keep these
    \PressGang\ServiceProviders\TimberServiceProvider::class,
    \PressGang\ServiceProviders\SeoServiceProvider::class,

    // Opt-in: convention-based template routing
    \PressGang\ServiceProviders\TemplateRoutingServiceProvider::class,
];
```
{% endcode %}

{% hint style="info" %}
Themes built on explicit template stubs are completely untouched by framework upgrades — nothing changes until you list the provider. And even once enabled, **a physical template file in your child theme always wins**, so you can adopt convention routing incrementally: delete stubs one at a time.
{% endhint %}

## How It Works

When WordPress resolves a request, PressGang records the template hierarchy candidates (most specific first). If the request falls through to a **parent-theme** template — meaning your child theme had no stub for it — the dispatcher resolves a controller from those candidates and renders it.

Mechanically: `template_include` must return a PHP file for WordPress to load, so the dispatcher hands it the parent theme's `dispatch.php` — a one-line landing file that calls `ControllerFactory::dispatch()`. The routing *decision* happens in the filter; `dispatch.php` is just where the request touches down. (Custom route handlers reuse the same landing file — see below.)

For each candidate, resolution tries:

1. **Your config map** — an explicit entry in `config/controllers.php`
2. **Naming convention** — a matching controller in your child theme's `Controllers` namespace

The matching `{candidate}.twig` in your `views/` directory renders automatically; if it doesn't exist, the controller's own template inference applies.

## The Naming Conventions

Beyond the literal StudlyCase name, the dispatcher understands WordPress template hierarchy semantics — matching the PressGang convention of **plural controllers for archives, singular for single views**:

| Candidate             | Resolves to           | Rule                               |
| --------------------- | --------------------- | ---------------------------------- |
| `search`              | `SearchController`    | StudlyCase                         |
| `front-page`          | `FrontPageController` | StudlyCase                         |
| `archive-event`       | `EventsController`    | `archive-{type}` → pluralised type |
| `single-event`        | `EventController`     | `single-{type}` → the subject      |
| `taxonomy-event-type` | `EventTypeController` | `taxonomy-{tax}` → the subject     |

Parent framework controllers are never matched by convention — the parent theme's own templates already route to them — so dispatch only activates for controllers _you_ define.

## Hyphenated Template Names

WordPress builds hierarchy candidates from your registered keys, so a taxonomy named `event_type` traditionally demands `taxonomy-event_type.php` — an underscore in a world of kebab-case filenames. With routing enabled, every underscored candidate gets a hyphenated twin, so `taxonomy-event-type.php` (and `taxonomy-event-type.twig`) work too. Underscored names keep working.

## The Config Map

Most themes need **no entries at all** — conventions cover the common cases. Use `config/controllers.php` only when a controller's name defies convention:

{% code title="config/controllers.php" lineNumbers="true" %}
```php
return [
    'archive-event' => \MyTheme\Controllers\WhatsOnController::class,
];
```
{% endcode %}

An explicit map entry always beats convention for its candidate.

## File-less Page Templates

Page templates traditionally require physical files for WordPress to discover their `Template Name:` headers. With routing enabled, register them declaratively instead — no `page-templates/` directory:

{% code title="config/page-templates.php" lineNumbers="true" %}
```php
return [
    'page-templates/contact-page.php' => 'Contact Page',
    'page-templates/grid-page.php'    => 'Grid Page',
];
```
{% endcode %}

Each registered template resolves to its `{Slug}Controller` by convention (`sidebar-page` → `SidebarPageController`), falling back to the framework `PageController`, and renders `{slug}.twig`.

{% hint style="info" %}
**Migrating an existing theme?** Use the legacy file-shaped ids shown above — they match the `_wp_page_template` values already stored on your pages, so assignments and the admin dropdown carry over with **no data migration**. New themes can use bare slugs like `'contact-page'`.
{% endhint %}

## When to Keep a Template File

A stub is still the right tool when there's genuine logic in template selection — the file always wins over dispatch, so nothing fights you:

{% code title="single-hit.php" lineNumbers="true" %}
```php
use MyTheme\Controllers\HitController;
use MyTheme\Controllers\SidebarPageController;

global $post;

if ( $post->post_parent === 0 ) {
    PressGang\PressGang::render( controller: HitController::class, twig: 'single-hit.twig' );
} else {
    PressGang\PressGang::render( controller: SidebarPageController::class, twig: 'sidebar-page.twig' );
}
```
{% endcode %}

## Custom Route Handlers

`config/routes.php` maps custom URL patterns (via the Upstatement Routes library) to a template filename — or, for routes that need logic before rendering, to a class implementing `RouteHandlerInterface`:

{% code title="config/routes.php" lineNumbers="true" %}
```php
return [
    'archive/:year'     => 'archive-year.php',
    'route/:slug/news/' => \MyTheme\Routes\NewsRoute::class,
];
```
{% endcode %}

A handler receives the matched route parameters and is responsible for loading a response — typically by building query args (Quartermaster's `toArgs()` is made for this) and handing off to the dispatcher:

{% code title="src/Routes/NewsRoute.php" lineNumbers="true" %}
```php
namespace MyTheme\Routes;

use PressGang\Quartermaster\Quartermaster;
use PressGang\Routes\RouteHandlerInterface;
use PressGang\Templates\TemplateHierarchy;

class NewsRoute implements RouteHandlerInterface {

    public function handle( array $params ): void {

        // This route renders the news listing regardless of what WP's
        // conditionals make of the query (empty paged listings flag 404).
        TemplateHierarchy::prepend( 'taxonomy-event-type' );

        \Routes::load(
            'dispatch.php',
            [ 'slug' => $params['slug'] ?? null ],
            Quartermaster::posts( 'post' )
                ->paged( (int) get_option( 'posts_per_page' ), (int) ( $params['paged'] ?? 1 ) )
                ->toArgs()
        );
    }
}
```
{% endcode %}

`TemplateHierarchy::prepend()` seeds the candidate your route _means_, so controller resolution stays deterministic even when WordPress's conditionals disagree (an empty page 2, for example).

## Precedence, In One Breath

**Template file → config map → naming convention.** Explicit always beats implicit, and your child theme always beats the framework.
