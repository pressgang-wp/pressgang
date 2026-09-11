# PressGang 2 — Architecture & Enforcement Rules

This is the **authoritative guide** for the PressGang 2 codebase.
All changes **must conform to these rules**.

---

## Architecture Overview

PressGang 2 is a **WordPress parent theme framework** built around:

- Composer autoloading with PSR-4 (`PressGang\\` → `src/`)
- Timber (v2) + Twig for rendering (see Timber v2 docs)
- Config-driven bootstrapping (`config/`)
- Controllers as **template-scoped view models**
- Context managers and Twig extensions to enrich templates

**Controllers are not request handlers or routers.**
They exist only to prepare data for templates.

---

## Boot Sequence (from `functions.php`)

1. Composer autoload
2. Instantiate `PressGang` with `Loader`
3. `PressGang::boot()`:
	- `Timber::init()`
	- `Loader::initialize()`: loads config, registers hooks, includes files — **no business logic**
	- `Service providers boot`: class strings from `config/service-providers.php` are instantiated and booted (by default includes `TimberServiceProvider` for context managers, Twig extensions, Twig environment options, and snippet paths)

**Never perform queries, I/O, or remote requests during boot.**

---

## Configuration System

All config files live in `config/` and **return arrays only**.

- Config defines **registration**, not execution — purely declarative.
- No queries, no logic branches, no side effects.
- Child theme config **overrides** parent config.
- Each config file maps to a class in `src/Configuration/` by studly-case convention
  (implemented in `Loader` via `u($key)->camel()->title(true)`):
	- `config/sidebars.php` → `PressGang\Configuration\Sidebars`
	- `config/custom-post-types.php` → `PressGang\Configuration\CustomPostTypes`
	- The class **must exist**; config alone does nothing.
- Config is cached. Invalidate only via `PressGang\Bootstrap\Config::clear_cache()`.

---

## Timber-First Data Access

When retrieving content or objects, **prefer Timber APIs**:

- `Timber::context()` for base context
- `Timber::get_post()`, `Timber::get_posts()`, `Timber::get_term()`, `Timber::get_terms()`
- Timber Post/Term objects in context over raw `WP_Post`/`WP_Term`

Use direct WordPress calls only when:
- Timber has no wrapper (e.g. `add_action`, `wp_enqueue_script`)
- You need capabilities, nonces, options/settings APIs

**Rule of thumb:** if data ends up in Twig, it should be a Timber object.

### ACF relationship/post-object values

Use Timber's existing bridge for audited presentation fields:
`$post->meta( 'x', [ 'transform_value' => true ] )`. ACF invokes Timber's
replacement type formatters recursively inside groups, repeaters and flexible
content. Containers remain arrays; relationship values can be
`Timber\PostArrayObject` collections. Existing Timber class maps apply.

**Do not enable `timber/meta/transform_value` globally.** Dates become
`DateTimeImmutable`, images/files stop following ACF's configured array/ID/URL
format, and relationships/taxonomies can no longer be used as query IDs.
Per-call adoption preserves migration parity for fields that are not opted in.

**Use one formatted mode per field/entity per request, including nested reads.**
On Timber 2.5.1 with ACF 6.8.9, ACF caches normal and transformed values under
the same key: a normal read can prevent transformation, and a transformed read
can leak objects into a later normal read. `transform_value => false` alone
is not a reliable raw-value escape after transformation. For query inputs, use
`raw_meta()` (WP storage) or both `transform_value => false` and
`format_value => false` (unformatted ACF). Neither promises normal ACF image
arrays or formatted date strings. Keep explicit mapping when those normal
ACF values must coexist with Timber objects.

Timber also leaves its temporary formatters installed if formatting throws.
Do not assume catching that exception restores ACF formatting for subsequent
reads. PressGang does not add a cache/hook wrapper or patch vendor code.

`PressGang\ACF\TimberMapper::to_timber_posts()` remains supported for existing
consumers and mixed-use fields. Its array return contract is unchanged; do not
pass a Timber collection into it. Options and block contexts keep the existing
mapper behaviour. New per-call adoption reuses Timber rather than extending
that mapper into a second recursive bridge.

See [ACF values and consuming-theme upgrade examples](docs/ACF-VALUES.md) for
return types, raw reads, the four penarc examples and measured limitations.

---

## Controllers and Rendering

Controllers live in `src/Controllers`.

**Controllers must:**
- Extend `AbstractController`
- Gather data, build context, select templates
- Prefer Timber objects in context (`Timber\Post`, `Timber\Term`, collections)
- Keep `get_context()` **side-effect free**

**Controllers must not:**
- Query request globals (`$_GET`, `$_POST`, `$_REQUEST`)
- Perform writes (DB/options/meta)
- Call `Timber::render()` directly
- Do remote requests or filesystem work

**Rendering flow** via `PressGang::render()`:
- Filters: `pressgang_{controller}_template`, `pressgang_{controller}_context`
- Action: `pressgang_render_{controller}`

**Context manifest** — instead of overriding `get_context()` to wire getters
one line at a time, declare the template contract:

```php
protected array $context_getters = [ 'news', 'events', 'hero_featured' ];
```

Each entry calls the matching `get_{key}()` (or `'key' => 'method'` to
override the name); applied after `get_context()`, before the context
filters. This is the controller counterpart to `HandlesDynamicGetters` on
models: getters own the fetching, the manifest declares which of them are
template API. The manifest must stay an explicit list — never auto-publish
all getters, or internal helpers silently become template contract.

One controller per template concern. Avoid "god controllers".

---

## Template Routing (Controllers Without Stubs)

Opt-in: enabled by listing `TemplateRoutingServiceProvider` in the child
theme's config/service-providers.php. Deliberately not a parent default —
the many existing stub-built PressGang themes must upgrade with zero
routing-behaviour change.

WordPress template hierarchy candidates are recorded per request
(`Templates\TemplateHierarchy`), and candidates containing underscores get
hyphenated twins (`taxonomy-event_type.php` -> also `taxonomy-event-type.php`)
so theme files can use kebab-case consistently.

When a request falls through to a **parent-theme** template, the dispatcher
(`Templates\TemplateDispatcher` -> `dispatch.php`) resolves a controller from
the candidates, most specific first:

1. `config/controllers.php` — explicit candidate => controller FQCN map
2. Convention — `{ChildNS}\Controllers\{StudlyCandidate}Controller`, plus
   hierarchy-semantic inflections: `archive-{type}` => pluralised
   `{Types}Controller`, and `single-{type}` / `taxonomy-{tax}` =>
   `{Subject}Controller` (so `archive-event` => `EventsController`,
   `taxonomy-event-type` => `EventTypeController` — zero config)

The candidate's `{candidate}.twig` is used when it exists in the child
`views/`; otherwise the controller's own template inference applies. A
physical template file in the child theme always wins over dispatch — use a
stub for conditional controller selection or Routes-loaded templates. Parent
framework controllers are never matched by convention (parent templates
already route to them), so dispatch only activates for theme-defined
behaviour.

Page templates are registered file-lessly via `config/page-templates.php`
(`Configuration\PageTemplates`, `theme_page_templates` filter). Use legacy
file-shaped ids when migrating so stored `_wp_page_template` values keep
working. The assigned template's slug is seeded as the leading candidate
(`TemplateHierarchy::prepend()`); registered slugs fall back to
`PageController` when no `{Slug}Controller` exists. Custom route handlers
can seed candidates the same way before loading `dispatch.php`.

---

## Context Managers

Context managers extend the global `Timber::context()`.

- Registered via `config/context-managers.php`
- Must implement `ContextManagerInterface`
- Must be safe on: frontend, admin, AJAX, CLI
- Assume execution on **every request**
- No heavy queries unless cached
- Only add data needed across many templates
- Wrap static calls and constructors (Timber, WP, ACF) in **protected methods** to keep the class unit-testable (see Testing > Testability seams)

---

## Twig Extensions

Registered via `config/twig-extensions.php`.

- All Twig functions, filters, and globals **must** be registered via extension managers.
- Twig functions must be: pure, deterministic, side-effect free.

### Twig is presentation only

**Forbidden in Twig:**
- Database queries or calls to WordPress/Timber APIs for data
- Accessing `$_GET`, `$_POST`, `$_REQUEST`
- Mutating context data
- Business logic
- Writes or remote calls

---

## Escaping (escape in Twig, sanitise in PHP)

### Timber ships autoescape OFF

`Timber::$autoescape` defaults to `false` and PressGang does not override it, so
`{{ value }}` prints **raw**. Every value a template prints must be escaped by
hand. Do not read the rules below as "Twig escapes for you".

Turning autoescape on globally is not a drop-in fix: templates that emit markup
on purpose would start escaping it. If a project wants it, that is a deliberate,
audited migration of every template — not a config flip.

### In Twig
- Text: `{{ value|e }}`
- Attributes: `{{ value|e('html_attr') }}`
- URLs: `{{ value|e('url') }}`
- Values meant to carry markup: `{{ value|e('wp_kses_post') }}`
- `{{ value|raw }}` (or a bare `{{ value }}`) **only when sanitised in PHP and
  explicitly intended to contain HTML**
- Do not call `esc_html()`, `esc_attr()`, etc. inside Twig — escape with `|e`
  filters so the strategy stays in one layer.

### In PHP (before Twig)
- **Sanitise input** in PHP. Do not pre-escape output intended for Twig.
- Pass clean, domain-correct values into context; escape them in the template.

### Sanitisation functions
- Text: `sanitize_text_field()`
- Textarea: `sanitize_textarea_field()`
- Email: `sanitize_email()`
- Keys: `sanitize_key()`

---

## Internationalisation (i18n)

All translations use a **single text domain**: `THEMENAME`

### PHP
- `__( 'Text', THEMENAME )`, `_e()`, `_x()`, `esc_html__()`
- All user-facing strings must be translatable.
- Do not hardcode alternative text domains or translate proper nouns.

### Twig
- `{{ __('Read more', THEMENAME) }}`
- `{{ __('View %s', THEMENAME)|format(title) }}`
- Do not concatenate translated strings; use placeholders.

---

## Blocks

Registered via `config/blocks.php`.

- Each block lives under `blocks/<block-name>/` with `block.json` and a Twig template.
- Block rendering must be a **pure function of block context**.
- No inline render callbacks with logic; no queries in block render paths.

---

## Snippets

Snippets are **self-contained, hook-based behaviours** — a class implementing
`PressGang\Snippets\SnippetInterface` that registers all its hooks in its
constructor and does one thing. They are not view partials and not logic
containers for a template.

- Registered via `config/snippets.php` as `name => constructor args`.
- Resolved by `Util\ClassResolver`: a fully qualified name is used as-is;
  otherwise `{ChildNamespace}\Snippets\{name}` then `PressGang\Snippets\{name}`.
- The `pressgang-wp/pressgang-snippets` library groups snippets into
  sub-namespaces (`Theme\`, `Content\`, `Acf\`, `Integration\`, `Seo\`,
  `Facebook\`, `Google\`, `WooCommerce\`), so library snippets **must** be named
  with the sub-namespace: `'Theme\DisableEmojis'`, not `'DisableEmojis'`.
- **Resolution fails silently.** A name matching no class is skipped with no
  error. Verify with `wp capstan snippets` / `wp capstan doctor`.
- Check the library for a 1:1 equivalent before writing a theme snippet.
- Must be self-contained and explicitly parameterised.

---

## Widgets, Shortcodes, Metaboxes

Loaded via `Loader::include_files()`.

- Implement under `src/Widgets`, `src/Shortcodes`.
- Keep logic minimal; delegate heavy work to services or helpers.
- Prefer blocks over shortcodes for new UI.

---

## Forms and Validation

Built around `PressGang\Forms\FormSubmission` and validators in `src/Forms/Validators`.

- All input must be sanitised and validated.
- Validation logic **must not** live in controllers.
- CSRF / nonce handling is mandatory.
- Hooks registered via `FormSubmission::register_hooks()`.
- Controllers may **consume validated data only**.

---

## Helpers and Utilities

Located in `src/Helpers` and `src/Util`.

- Must be stateless and deterministic. No hidden global state.
- If state is required, use a service class.
- Avoid tight coupling to global WP functions.

---

## Assets (Scripts and Styles)

Configured via `config/scripts.php` and `config/styles.php`.

- All assets must be registered via config.
- No direct enqueues in templates.
- Versioning / cache-busting is handled in config.

---

## SEO

Utilities live under `src/SEO`.

- Use `MetaDescriptionService` where applicable.
- Single source of truth for meta output; never duplicate meta tags.

---

## WordPress Security Conventions

Any state change requires:
- Capability check (`current_user_can()`)
- Nonce validation (`check_admin_referer()` / `wp_verify_nonce()`)

Never trust request input; always sanitise and validate in PHP.

Use `add_action()` / `add_filter()` as the primary extension mechanism.
Always register hooks from namespaced classes.

---

## Parent vs Child Theme Responsibilities

| Parent Theme | Child Theme |
|---|---|
| Framework, infrastructure, defaults, shared services | Site-specific behaviour, templates, branding, overrides |

- Parent must not assume site knowledge.
- Child must not modify parent internals directly.
- Extend via config, filters, hooks, template overrides.

---

## Performance Guardrails

- Assume archive controllers execute **N times**.
- Cache any non-trivial query (object cache / transients).
- Avoid N+1 meta access; batch or cache.
- Never introduce uncached queries in: context managers, Twig, boot paths.

---

## Code Style

- Modern PHP: typed properties, return types, small cohesive classes.
- PSR-4 autoloading via Composer; all classes under `src/`.
- Namespace everything (`PressGang` or child namespace). No global functions.
- Prefer dependency injection for services.
- Prefer hooks and filters over direct modification.
- Do not overwrite required `Timber::context()` keys.
- Wrap static calls, constructors, and global helpers in **protected methods** to create testability seams (see Testing > Testability seams). Widen return types to `object` or `mixed` when a subclass override needs flexibility.

---

## Canonical Extension Pattern

When adding a new feature:

1. Add config in `config/<feature>.php`
2. Create `src/Configuration/<Feature>.php`
3. Expose data via controllers, context managers, or Twig extensions
4. Place templates in `views/`, blocks in `blocks/`

This pattern is **non-optional**.

---

## Implementing a Change (Workflow)

1. **Choose the correct layer:**
	- Markup/UI → Twig template/partial
	- Data for template → Controller context (Timber-first)
	- Computation/business rules → Service/Helper
	- Global shared data → Context manager (cached if non-trivial)
	- Registration/wiring → Config
2. **Fetch content Timber-first.** Convert raw WP objects to Timber objects before Twig.
3. **Apply WP security conventions** for writes: capability checks + nonces + sanitisation + validation.
4. **Run `composer test` and `composer phpstan`** to verify nothing is broken.
5. **Keep diffs minimal.** Do not refactor unrelated code unless instructed.

---

## Hard Failures (never do this)

- Put queries or business logic in Twig
- Add heavy queries to context managers without caching
- Perform remote requests or filesystem writes during render
- Introduce new global PHP functions
- Modify vendor code or WordPress core
- Expose raw `WP_Post`/`WP_Term` to Twig when Timber objects are feasible
- Mix escaping strategies across layers
- Output unsanitised user input with `|raw`

---

## Known Constraints

- `composer.json` requires PHP `^8.3`; keep `readme.txt` consistent.
- Do not assume a clean working tree during development.
- Functions loaded via Composer `files` autoload (e.g. `config()` from `src/Helpers/helper.php`) are defined before BrainMonkey and cannot be mocked at runtime. Extract calls to these into protected methods for testability.

---

## Doc Blocks (PHP)

Doc blocks are part of PressGang’s internal documentation. They must help developers who are new to PressGang
understand **where the code sits in the architecture**, **why it exists**, and **how to extend it safely**,
while remaining concise and useful for static analysis.

### General rules
- Prefer **short, informative** doc blocks over boilerplate.
- Document **intent + invariants** (what must remain true), not step-by-step control flow.
- Keep framework-level primitives more documented than leaf-level glue.
- Remove redundant noise: `@package`, `Class ClassName` headers, restating obvious types already in signatures.

### Class doc blocks
Classes fall into tiers:

- Tier A (framework primitives / extension points): Controllers, Configuration classes, Context managers, Twig extensions, Forms/Validators.
	- Write **2–5 lines** explaining:
		- responsibility and boundaries (what it does / does not do)
		- why it exists (PressGang convention)
		- extension mechanism (filter/config override/child class)
- Tier B (thin glue / simple adapters): 1–2 sentences.
- Tier C (trivial): 0–1 sentence acceptable.

Example (Tier A):

    /**
     * Registers custom post types defined in config and applies PressGang label conventions.
     *
     * Why: keeps registration declarative and consistent across parent/child themes.
     * Extend via: child theme config override or filters applied in this configuration class.
     */
    class CustomPostTypes extends ConfigurationSingleton

### Method doc blocks
Always include `@param` and `@return`. Use generics where helpful (e.g. `array<string, mixed>`).

- Include a brief description when the method name alone is not self-explanatory.
- If the method enforces an invariant, caches, or applies a framework convention, document that briefly.
- Trivial getters/setters may omit the description line, but should still carry `@param`/`@return` if they accept args/return values.

Example:

    /**
     * Adds the current post to context under both 'post' and a post-type-specific key.
     *
     * Invariant: context keys must remain stable for Twig templates and child theme overrides.
     *
     * @param \Timber\Post $post
     * @return array<string, mixed>
     */
    protected function get_context( Post $post ): array

### Inline `@var` annotations (strongly preferred)
Use `@var` whenever a variable's type is inferred, cast, or returned from a framework call
(Timber/WP commonly returns unions or mixed arrays).

    /** @var \Timber\Post $post */
    $post = Timber::get_post();

    /** @var \Timber\Post[] $posts */
    $posts = Timber::get_posts( $args );

    /** @var array<string, mixed> $context */
    $context = Timber::context();

### Recommended tags (use when relevant)
- `@since` for public-ish framework surfaces that consumers may rely on
- `@see` to point to the related hook, config key, or extension point
- `@deprecated` with a replacement path
- `@throws` when exceptions are intentionally surfaced

(WordPress uses tags like `@since`, `@see`, `@global` heavily; follow WP conventions where they fit.)

### What to avoid
- Paragraph-length prose restating what the code already shows.
- `@package` tags (redundant with PSR-4 namespaces).
- `Class ClassName` / `Interface InterfaceName` header lines.
- `@return mixed` when a concrete return type exists in the signature.
- Doc blocks used as control-flow comments.

---

## Known Exceptions

These are intentional deviations from the general rules documented above. They exist for
good reasons and should **not** be "fixed" without understanding the context.

### `$_POST` access in `Metabox::save_post_meta()`
The general rule forbids direct `$_POST` access, but the `save_post` hook requires it for
nonce verification and field value retrieval. The access is guarded by nonce checks and
capability checks, which is the standard WordPress pattern.

### Global mutation in `WooCommerceExtensionManager::timber_set_product()`
The `timber_set_product()` Twig function sets `global $product`. This is required by
WooCommerce — product templates do not receive the correct context without it.
See the [Timber WooCommerce docs](https://timber.github.io/docs/v2/guides/woocommerce/#tease-product).

### `orderby => 'rand'` in `Post::fetch_related_posts()`
Random ordering is normally discouraged for performance, but it is acceptable here because
the results are cached via `wp_cache` with a configurable TTL (`PRESSGANG_CACHE_TIME`).

### Repeater/flexible_content mapping in `TimberMapper`
The legacy mapper passes nested values through unchanged. Keep that behaviour
for existing options/block consumers. Timber's per-call ACF bridge already
supports recursive formatting; a bespoke recursive mapper is not planned.
The `term` case is retained for compatibility, but ACF's standard field type
is `taxonomy`, which Timber supports. See [ACF values](docs/ACF-VALUES.md).

### `Templates` class — potential deprecation
The `Templates` configuration class carries a `TODO maybe deprecate` marker. WordPress
now supports declaring custom page templates via the `Template Name:` header in template
files, which may make this class unnecessary in future.

### `ConfigurationSingleton` uses `static` return type
The `get_instance(): static` pattern ensures each subclass gets its own singleton instance
rather than sharing one. This is intentional PHP 8.0+ late-static-binding behaviour.

### Direct WP calls in controllers
Controllers occasionally call WordPress functions directly (e.g. `get_search_query()`,
`get_queried_object_id()`, `get_query_var()`) when Timber has no equivalent wrapper.
This is permitted under the Timber-first rules.

### Widened return types on context manager testability seams
Protected methods like `MenuContextManager::get_menu()` and `SiteContextManager::make_site()`
return `?object` / `object` instead of `?Menu` / `Site`. PHP does not allow widening return
types in subclass overrides, and the anonymous-subclass test pattern requires it. The docblock
`@return` retains the concrete type for IDE support.

### `trait.unused` ignored in PHPStan
`phpstan.neon.dist` ignores the `trait.unused` identifier only for known extension-point
traits. pressgang-wp is a parent-theme framework — traits like `HandlesDynamicGetters` and
`HasNoFunctions` exist to be consumed by child themes, not by this repo itself, so PHPStan's
"used zero times" check is structurally always wrong for those specific files.

### `property.notFound` ignored in `CustomMenuItems.php`
`wp_setup_nav_menu_item()` dynamically adds properties (`menu_item_parent`, `object_id`,
`url`, etc.) to `WP_Post` at runtime. They're real and documented but not part of the
`WP_Post` stub, so PHPStan can't see them; the ignore is scoped to this one file.

### WooCommerce runtime PHPStan stub
`phpstan-stubs/woocommerce-runtime.stub` corrects `WooCommerce::$cart` to `WC_Cart|null`.
WooCommerce initializes the cart lazily during the WordPress request lifecycle, so context
managers can genuinely see it unavailable even when WooCommerce itself is active.

---

## Repo Layout

- Theme root: project root
- Views: `views/`
- Blocks: `blocks/`
- PHP source: `src/`
- Config: `config/`
- Tests: `tests/` (mirrors `src/` structure under `tests/Unit/`)
- Composer: `composer.json`
- PHPUnit config: `phpunit.xml.dist`

---

## Testing

### Stack

- **PHPUnit 9.6** + **yoast/wp-test-utils ^1.2** (matches Timber 2's own test stack)
- BrainMonkey for mocking WordPress functions without a running WordPress installation
- Unit tests only (no WordPress integration tests yet)

### Running tests

```bash
composer test           # alias for test:unit
composer test:unit      # run the full unit suite
composer test:compat    # strict PHP compatibility/unit pass
composer check          # local convenience: test:compat + phpstan
vendor/bin/phpunit --filter ConfigTest          # single class
vendor/bin/phpunit --filter loads_and_caches    # single test by name
```

### Static analysis

- **PHPStan 2.x** at level 8, with `szepeviktor/phpstan-wordpress`, `php-stubs/woocommerce-stubs`,
  and `php-stubs/acf-pro-stubs` for WP/WooCommerce/ACF-aware type checking.
- Config: `phpstan.neon.dist`. There is no PHPStan baseline in this repo; new
  findings should be fixed rather than baselined.
- Remaining ignored identifiers are architectural, not suppressed bugs — see Known Exceptions below.

```bash
composer phpstan         # run PHPStan against src/
```

`composer check` is intentionally narrow: it runs `test:compat` and `phpstan`
only. Keep CI split into separate steps for clearer failure attribution.
Agents should run `composer check` when a child theme provides it; otherwise
run the theme's documented test and static-analysis commands separately.
Prefer source, PHPDoc, project stubs, or config fixes over new ignores, and do
not add baselines unless explicitly requested.

### Tooling boundaries

- PHPStan handles static PHP type/convention pressure.
- Capstan handles runtime introspection: `wp capstan resolve`, `context`,
  `config dump`, `snippets`, and `doctor`.
- Shakedown asserts runtime behaviour in CI with Capstan's
  `wp capstan matrix --resolve` oracle.
- `doctor` stays runtime-only, deterministic, fast, and heuristic-free; do not
  fold PHPStan-style checks into it.
- `wp capstan check` is unbuilt and blocked until at least one child theme has
  a working `composer check`; if built, it must only shell out to that command,
  run `doctor`, and report both.
- Keep shared stubs/ignore-list extraction separate from the convention extension
  below; share those only when a second repo independently needs the same corrections.

### Directory structure

```
tests/
├── bootstrap.php              # autoloader + THEMENAME/ABSPATH constants
└── Unit/
    ├── TestCase.php           # base class (extends YoastTestCase)
    ├── Blocks/
    ├── Bootstrap/             # Config, FileConfigLoader, Loader
    ├── Configuration/         # Sidebars, Menus, CustomPostTypes, Actions
    ├── ContextManagers/       # Menu, Site, ThemeMods, AcfOptions, WooCommerce
    └── ServiceProviders/      # TimberServiceProvider
```

### Writing a new test

1. Create the test class under `tests/Unit/` mirroring the `src/` path.
2. Extend `PressGang\Tests\Unit\TestCase` (provides BrainMonkey setup, singleton reset, `$_POST` helpers).
3. Use `Brain\Monkey\Functions\expect()` to mock WordPress functions.
4. Use `$this->resetSingletonInstances()` in `set_up()`/`tear_down()` for any test that touches a `ConfigurationSingleton` subclass.
5. For classes that call `Config::get()`, set up a stub `ConfigLoaderInterface` and call `Config::set_loader()` + `Config::clear_cache()` in teardown.

### Testability seams (protected method pattern)

Several classes depend on static calls or constructors that are impossible to mock directly
(e.g. `Timber::get_menu()`, `new Site()`, `TimberMapper::map_field()`). These are wrapped
in **protected methods** that tests override via anonymous subclasses:

| Class | Protected method | Wraps |
|---|---|---|
| `MenuContextManager` | `get_menu($location)` | `Timber::get_menu()` |
| `SiteContextManager` | `make_site()` | `new Timber\Site()` |
| `AcfOptionsContextManager` | `is_acf_active()` | `function_exists('get_fields') && config(...)` |
| `AcfOptionsContextManager` | `map_field($field)` | `TimberMapper::map_field()` |
| `WooCommerceContextManager` | `is_woocommerce_active()` | `class_exists('WooCommerce')` |
| `WooCommerceContextManager` | `build_links()` | WC link-building calls |
| `WooCommerceContextManager` | `get_cart_contents_count()` | `WC()->cart->get_cart_contents_count()` |

**Example — testing with an anonymous subclass:**

```php
private function makeManager(): MenuContextManager {
    return new class(['primary' => $menuObj]) extends MenuContextManager {
        public function __construct(private readonly array $menuMap) {}
        protected function get_menu(string $location): ?object {
            return $this->menuMap[$location] ?? null;
        }
    };
}
```

**When adding a new dependency that is hard to mock** (static call, global function loaded
via Composer `files`, constructor with side effects), extract it to a protected method with
a clear name and a docblock noting the production implementation. This keeps the class
testable without introducing interfaces or constructor parameters for every external call.

### ConfigurationSingleton::reset_instances()

`ConfigurationSingleton` exposes a static `reset_instances()` method for test isolation.
Call it in `set_up()` and `tear_down()` for any test that calls `get_instance()` on a
Configuration subclass. The base `TestCase` wraps this as `$this->resetSingletonInstances()`.

### BrainMonkey tips

- `apply_filters` receives `($hook, $value, ...$extra)`. To pass through the value unchanged,
  use `andReturnUsing(fn() => func_get_args()[1])` — **not** `andReturnFirstArg()` (which returns the hook name).
- `wp_parse_args` is pre-stubbed by `YoastTestCase` to behave like `array_merge($defaults, $args)`.
- Functions already defined before BrainMonkey (e.g. Composer `files` autoload helpers like `config()`)
  cannot be mocked — extract them to protected methods instead.
- Avoid `@runTestsInSeparateProcesses` when possible; it is 5–10x slower. Prefer the protected method
  pattern over Mockery alias mocks for already-loaded classes.


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

## Lean controller context

Expose additional prepared data, not a duplicate of every model field. Read
presentation-only metadata with `post.meta('intro_title')` or `term.meta()` in
Twig, retaining the appropriate output escaping. Local Twig variables are useful
for repeated fields. Keep query construction, relationship normalization and
selection/enrichment rules in PHP.

A manifest invokes each entry once per application; it is not a general getter
cache. Add a cache only when another getter or execution path needs the same
result. Avoid one-use getter/resolver pairs. Before removing context keys, inspect
inherited block bodies, includes, macro arguments, dynamic access and PHP hooks.

For custom page listings, pass the displayed collection's `pagination()` to the
partial explicitly when this removes forwarding getters. Keep PostsController's
inherited pagination for ordinary archives. PostQuery caches pagination itself;
retain collection caches only for actual reuse, including headings/enrichment.
Metadata access on an existing model and pagination on an existing collection
are presentation operations; the prohibition on Twig queries concerns building
or executing independent queries, not these model APIs.

## PressGang PHPStan extension

`pressgang-wp/phpstan` (sibling checkout `../pressgang-phpstan`) analyses pure-source
contracts: controller `context_getters`, getter-backed model properties and
`meta()` calls, and direct getter/meta recursion. Child themes install it with
`composer require --dev pressgang-wp/phpstan:@dev phpstan/extension-installer`
after adding a Composer path repository for the local checkout (until published).
Allow `phpstan/extension-installer`; it automatically includes `extension.neon`.
Continue using the WordPress extension and ACF stubs, with theme source analysed
at an appropriate level. The framework remains at level 8.

Include `vendor/pressgang-wp/phpstan/rules.neon` explicitly for advisory orphan
getter checks. An intentional helper can use `@pressgang-context-helper` on its
method PHPDoc. An absent manifest entry is not proof of dead code; inherited
helpers and direct callers still matter. PHPStan treats opt-in advice as ordinary
diagnostics, with no separate warning exit status.

`AbstractController` currently does not use `HandlesDynamicGetters`; model typing
requires actual trait use. Getter-backed meta dispatch precedes Timber/ACF
transformation; other meta fields retain their declared types. Runtime config,
snippet resolution and bootstrap checks remain with `wp capstan doctor`.
See the [PHPStan extension guide](docs/PHPSTAN.md) for generic adoption examples,
diagnostics and limitations.
