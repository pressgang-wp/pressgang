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

### Verify with Capstan, don't guess

When the `wp capstan` WP-CLI package is installed, PressGang's conventions
are inspectable — prefer these over inferring from source:

- `wp capstan resolve <url>` — which controller handles a URL, via which
  hierarchy candidate. Run it after adding a controller or changing
  routing config to confirm resolution.
- `wp capstan context <Controller>` — a controller's manifest, its
  getters, and which are unpublished. After writing getters, publish them
  explicitly: `wp capstan context <Controller> --add=<keys> --force`
  (never hand-expand a manifest you haven't read first).
- `wp capstan config dump [<key>]` — the merged config the theme actually
  boots with; `wp capstan snippets` — registered snippets and their args.
- `wp capstan doctor` — run after config or composer changes; it catches
  missing classes and shadowed page templates deterministically.

Scaffold rather than hand-write boilerplate — each previews first and
writes with `--force`: `wp capstan make controller <Name> --type=posts|post|page`,
`wp capstan make cpt <slug>`, `wp capstan make block <slug>`.
