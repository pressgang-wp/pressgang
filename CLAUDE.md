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
2. Instantiate `PressGang` with `Loader` and `TimberServiceProvider`
3. `PressGang::boot()`:
	- `Timber::init()`
	- `Loader::initialize()`: loads config, registers hooks, includes files — **no business logic**
	- `TimberServiceProvider::boot()`: registers context managers, Twig extensions, snippet paths

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

One controller per template concern. Avoid "god controllers".

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

## Escaping (Twig escapes, PHP sanitises)

### In Twig (preferred)
- Auto-escaping must be enabled; `{{ value }}` escapes by default.
- Attributes: `{{ value|e('html_attr') }}`
- URLs: `{{ value|e('url') }}`
- `{{ value|raw }}` **only when sanitised in PHP and explicitly intended to contain HTML**
- Do not call `esc_html()`, `esc_attr()`, etc. inside Twig.

### In PHP (before Twig)
- **Sanitise input** in PHP. Do not pre-escape output intended for Twig.
- Pass clean, domain-correct values into context; let Twig handle escaping.

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

Snippets are **view partials** (reusable UI components), not logic containers.

- Registered via `config/snippets.php`, exposed to Twig via `TimberServiceProvider`.
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
4. **Run `composer test`** to verify nothing is broken.
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

### Commented-out repeater/flexible_content mapping in `TimberMapper`
The recursive mapping of ACF repeater and flexible_content sub-fields is intentionally
commented out. It was deferred pending a stable recursive strategy that handles all
ACF field type edge cases.

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
vendor/bin/phpunit --filter ConfigTest          # single class
vendor/bin/phpunit --filter loads_and_caches    # single test by name
```

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
