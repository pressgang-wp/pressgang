## PressGang 2 (parent theme framework)

<!-- Shipped by pressgang-wp; overrides bosun's built-in baseline. -->

This theme is a PressGang 2 child theme: Composer-autoloaded PSR-4 (`src/`),
Timber 2 + Twig rendering, and config-driven bootstrapping.

- Config files in `config/` return arrays only — registration, never logic.
  Each maps to a `PressGang\Configuration\{Studly}` class by filename.
- Controllers are template-scoped view models in `src/Controllers/`:
  side-effect free, no request globals, no writes, no direct rendering.
- Declare a controller's template contract with a context manifest:
  `protected array $context_getters = [ 'news', 'events' ];` — each key is
  populated from its `get_{key}()` getter. Never auto-publish getters.
- Data that reaches Twig should be Timber objects. Convert raw ACF
  relationship/post-object values with
  `PressGang\ACF\TimberMapper::to_timber_posts( $value )`; do not enable
  Timber's global `timber/meta/transform_value` filter.
- Twig is presentation only: no queries, no request globals, no business
  logic. Twig escapes (`|e`), PHP sanitises — never `esc_*` in Twig.
- Inspect and run the site with WP-CLI (`wp eval`, `wp db query`) and
  `wp capstan` commands where available.
