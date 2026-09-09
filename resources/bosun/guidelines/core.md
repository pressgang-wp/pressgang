## PressGang 2 (parent theme framework)

<!-- Shipped by pressgang-wp; overrides bosun's built-in baseline. -->

This theme is a PressGang 2 child theme: Composer-autoloaded PSR-4 (`src/`),
Timber 2 + Twig rendering, and config-driven bootstrapping.

- Config files in `config/` return arrays only — registration, never logic.
  Each maps to a `PressGang\Configuration\{Studly}` class by filename.
- Controllers are template-scoped view models in `src/Controllers/`:
  side-effect free, no request globals, no writes, no direct rendering.
- Name single-post/page controllers in the singular (`ConferenceController`)
  and collection/archive controllers in the plural (`ConferencesController`).
  Do not add `Single` to the name or derive it blindly from a legacy template
  filename. A collection landing page uses a plural name even with PageController.
  Taxonomies use their subject; special views keep names such as SearchController.
  On renames, preserve template IDs and update explicit routing and hook consumers.
- Declare a controller's template contract with a context manifest:
  `protected array $context_getters = [ 'news', 'events' ];` — each key is
  populated from its `get_{key}()` getter. Never auto-publish getters.
- Data that reaches Twig should be Timber objects. Convert raw ACF
  relationship/post-object values with
  `PressGang\ACF\TimberMapper::to_timber_posts( $value )`; do not enable
  Timber's global `timber/meta/transform_value` filter.
- Twig is presentation only: no queries, no request globals, no business
  logic. Escape in Twig, sanitise in PHP — never `esc_*` in Twig.
- **Timber ships Twig autoescape off**, so `{{ value }}` prints raw HTML and
  every value a template prints needs escaping by hand: `|e` in text,
  `|e('html_attr')` in attributes, `|e('wp_kses_post')` where the value is
  meant to carry markup. Do not read the rule above as "Twig escapes for
  you". Turning autoescape on globally is not a drop-in fix either — the
  templates that emit markup on purpose would start escaping it.
- Snippets are self-contained hook-based behaviours registered in
  `config/snippets.php`, not view partials. Library snippets carry their
  sub-namespace (`'Theme\DisableEmojis'`, not `'DisableEmojis'`), and a name
  that resolves to nothing is skipped **silently** — check `wp capstan
  snippets` / `wp capstan doctor` after editing the config.

### Verify static PHP changes with Composer

When the theme provides `composer check`, run it before handoff. It is a
local-dev convenience alias for exactly `test:compat` plus `phpstan`; CI may
keep those as separate jobs for clearer failure attribution.

If `composer check` is missing, run the theme's documented test and static
analysis commands separately. Prefer fixing source, PHPDoc, project stubs, or
configuration shape over adding PHPStan ignores. Do not add baselines unless a
maintainer explicitly asks.

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
  missing classes and shadowed page templates deterministically. It is
  runtime-only and must not be treated as a PHPStan replacement.

Scaffold rather than hand-write boilerplate — each previews first and
writes with `--force`: `wp capstan make controller <Name> --type=posts|post|page`,
`wp capstan make cpt <slug>`, `wp capstan make block <slug>`.

Do not invent route/controller/context/config validation. Use `wp capstan
resolve`, `wp capstan context`, `wp capstan config dump`, and Shakedown's
`wp capstan matrix --resolve` oracle. A future `wp capstan check` would only
orchestrate the theme's existing `composer check` plus `doctor`; it should not
be built until real child-theme adoption exists.


### Controller template mappings

Use a parent controller when a view needs no additional context. In
`config/controllers.php`, map a hierarchy candidate to a class string or
`[ 'controller' => PageController::class, 'template' => 'page/research-subpage.twig' ]`
(with the appropriate imported or fully qualified controller class). The explicit
Twig path takes precedence over candidate discovery. Omitting `template`, or
setting it to null, retains candidate discovery and constructor defaults.
Candidate specificity and physical child PHP template precedence are unchanged.
Keep custom controllers for additional getters, traits or behaviour. Before
removing a class, check its class-derived rendering filters and action consumers;
reusing the parent changes those hook names. This does not accept arbitrary
constructor arguments.


### Quartermaster search input

Prefer standard search bindings over callbacks that sanitize or URL-decode input.
`Binder::relevanssi('project-search', allowEmpty: true, default: '')` deliberately
applies an empty search when the query variable is missing/null. Without an
explicit default, missing/null inputs remain skipped. Malformed non-scalar input
is skipped; explicit empty values do not use defaults. The builder owns text
sanitization. Never double-decode GET values: literal plus signs must survive.
Keep HTML escaping in Twig. See docs/QUARTERMASTER.md for full semantics.
