# SEO

PressGang includes a built-in `MetaDescriptionService` that generates smart, cached meta descriptions for every page on your site. No need for a full SEO plugin just to get decent meta tags — PressGang has you covered on this voyage.

## MetaDescriptionService

The `MetaDescriptionService` generates a single, authoritative meta description for the current page. It's available in Twig via the `meta_description()` function (registered by the `MetaDescriptionExtensionManager`).

### Usage in Twig

{% code title="views/layouts/base.twig" %}
```twig
<meta name="description" content="{{ meta_description() }}">
```
{% endcode %}

That's it. One line in your `<head>` and PressGang handles the rest.

## Fallback Chain

The service uses a smart fallback chain to find the best description for each page type:

<details>
<summary><strong>Posts and Pages</strong></summary>

1. **Yoast SEO meta description** (`_yoast_wpseo_metadesc` post meta) — if Yoast is installed and a description is set, it takes priority.
2. **Custom field** (`meta_description` post meta) — a manual override without needing Yoast.
3. **Post excerpt** — the WordPress excerpt field.
4. **Post content** — falls back to the rendered content.
5. **Site tagline** — the default site description from Settings > General.

</details>

<details>
<summary><strong>Taxonomy Terms</strong></summary>

1. **Yoast SEO taxonomy meta** — if configured in Yoast's taxonomy settings.
2. **Term description** — the description field from the term editor.
3. **Site tagline** — the default fallback.

</details>

<details>
<summary><strong>Archives</strong></summary>

1. **Archive description** — via `get_the_archive_description()`.
2. **Site tagline** — the default fallback.

</details>

<details>
<summary><strong>Front Page</strong></summary>

Always uses the **site tagline** (from Settings > General > Tagline).

</details>

## Truncation

Descriptions are automatically truncated to **155 characters** (the recommended SEO limit) using smart boundary detection:

1. If the description fits within 155 characters, it's used as-is.
2. If it's longer, PressGang looks for the last full stop (period) within the limit and cuts there.
3. If no sentence boundary is found, it trims to the last complete word.

This ensures descriptions always read naturally — no awkward mid-word cutoffs.

## Caching

Meta descriptions are cached per object using `wp_cache`, so the fallback chain only runs once per page per request cycle. The cache key is based on the object type and ID.

## Hooks

| Hook | Type | Available in |
|---|---|---|
| `pressgang_contact_to_email` | filter | ContactSubmission |

{% hint style="success" %}
The `MetaDescriptionService` works alongside Yoast SEO rather than competing with it. If Yoast is installed, its per-page descriptions always take priority. PressGang simply provides a sensible fallback chain for pages where Yoast hasn't been configured.
{% endhint %}

## Configuration

The `MetaDescriptionExtensionManager` is registered by default in `config/twig-extensions.php`. No additional configuration is needed — just use `{{ meta_description() }}` in your templates.

To add `meta_description()` support to a theme, ensure the extension manager is listed:

{% code title="config/twig-extensions.php" %}
```php
return [
    \PressGang\TwigExtensions\GeneralExtensionManager::class,
    \PressGang\TwigExtensions\MetaDescriptionExtensionManager::class,
    // ...
];
```
{% endcode %}
