---
description: >-
  Static analysis for PressGang controller manifests, dynamic model getters
  and getter-backed metadata access in child themes.
---

# PHPStan extension

`pressgang-wp/phpstan` teaches PHPStan about conventions that ordinary PHP type
checking cannot see: controller context manifests and model getter dispatch.
It analyses PHP source without booting WordPress or querying a database.

Use it alongside `szepeviktor/phpstan-wordpress` and the plugin stubs your theme
needs. It does not replace those packages or [Capstan's runtime checks](CAPSTAN.md).

## Install in a child theme

The extension requires PHP 8.2+ and PHPStan 2.2.9+. PressGang itself requires PHP
8.3+, so the framework requirement still applies to your theme.

For the current local, unpublished package, add a path repository pointing to
your checkout. Replace the example path with its location on your machine:

```bash
composer config repositories.pressgang-phpstan path /path/to/pressgang-phpstan
composer config allow-plugins.phpstan/extension-installer true
composer require --dev pressgang-wp/phpstan:@dev phpstan/extension-installer:^1.4 \
  szepeviktor/phpstan-wordpress:^2.0
```

If the theme uses ACF Pro, also install its analysis stubs:

```bash
composer require --dev php-stubs/acf-pro-stubs:^6.8
```

`phpstan/extension-installer` automatically loads the package's `extension.neon`.
Do not include it again in your theme config. Without the installer, include
`vendor/pressgang-wp/phpstan/extension.neon` manually.

Start with a `phpstan.neon.dist` appropriate to your theme:

```neon
parameters:
    level: 5
    paths:
        - src
    scanFiles:
        - vendor/php-stubs/acf-pro-stubs/acf-pro-stubs.php
```

Omit `scanFiles` if you did not install the ACF stubs. The theme, framework and
Timber classes must be discoverable through Composer autoloading. A migration
can start at level 5 and increase its level as findings are resolved; level 8
remains the framework's standard and the target for child themes.

Add a script to the theme's existing Composer scripts, preserving its other checks:

```json
{
    "scripts": {
        "phpstan": "phpstan analyse --memory-limit=1G"
    }
}
```

Run `composer phpstan`, and include it in your existing `composer check` workflow.
See [Testing](TESTING.md) for the broader testing conventions.

## Controller context manifests

A controller's manifest declares which getters supply template context:

```php
namespace YourTheme\Controllers;

use PressGang\Controllers\AbstractController;

class ArticlesController extends AbstractController {
    /** @var array<int|string, string> */
    protected array $context_getters = [
        'heading',
        'summary' => 'build_summary',
    ];

    /** @return string */
    protected function get_heading(): string {
        return 'Articles';
    }

    /** @return string */
    protected function build_summary(): string {
        return 'Recent updates';
    }
}
```

The numeric entry `'heading'` calls `get_heading()`. The alias entry calls
`build_summary()` and places its value under `summary`. A missing method produces
`pressgang.contextGetterMissing`, rather than waiting for the page to execute
the dynamic call.

Getters may be inherited from parent controllers or supplied by traits. An
inherited manifest is checked against the child class; a child declaration
replaces the parent manifest rather than merging with it. Manifest references
also participate in PHPStan's unused-method extension point.

The resolver reads source-reflected property defaults. Keep manifests declarative:
it does not follow constructor mutations or runtime assignments, and it skips
defaults it cannot resolve to string entries.

## Getter-backed model properties and metadata

A Timber model using `HandlesDynamicGetters` exposes a getter through both
property access and `meta()`:

```php
namespace YourTheme\Models;

use PressGang\Traits\HandlesDynamicGetters;
use Timber\Post;

class Article extends Post {
    use HandlesDynamicGetters;

    /** @return string */
    public function get_display_title(): string {
        return $this->post_title;
    }
}
```

For an `Article`, both `$article->display_title` and
`$article->meta('display_title')` now have type `string`. Nullable types and
PHPDoc generics are preserved, including for inherited or trait getters.

Virtual properties are readable, not setters. Native properties retain their
own types. Only actual getter methods count; an arbitrary `@method` annotation
does not establish a getter contract.

`meta()` narrowing requires one known constant-string field name. Dynamic keys,
unknown getters, uncertain unions, unpacked arguments and the falsey names `''`
and `'0'` keep the declared return type. Named arguments are supported.

Getter dispatch happens before `parent::meta()`. Timber's `transform_value`
option therefore does not change a custom getter's declared return type.
Ordinary ACF fields retain their declared type, usually `mixed`; the extension
does not infer field shapes from runtime ACF configuration. See [ACF Values](ACF-VALUES.md)
for transformation behaviour.

The trait is detected through inheritance and nested traits. Overridden
`__get`, `meta`, `has_custom_getter` or `call_custom_getter` implementations are
left to their declared behaviour. The supported getter-argument resolution hook
may still be overridden. `AbstractController` itself currently does not use
`HandlesDynamicGetters`.

### Non-Timber trait users

The property extension and recursion rule apply to all trait users. PHPStan's
method return-type API registers against a class or interface, so the default
`meta()` registration targets `Timber\CoreEntity`. For another base class using
the trait, add a registration:

```neon
services:
    -
        class: PressGang\PHPStan\Type\MetaReturnTypeExtension
        arguments:
            baseClass: YourTheme\Models\ModelBase
        tags:
            - phpstan.broker.dynamicMethodReturnTypeExtension
```

## Avoiding getter/meta recursion

This getter calls itself indirectly through the trait:

```php
/** @return mixed */
public function get_label(): mixed {
    return $this->meta( 'label' );
}
```

The extension reports `pressgang.recursiveMetaGetter`. Either rename the method
without the `get_` prefix, or read the parent implementation directly:

```php
/** @return mixed */
public function get_label(): mixed {
    return parent::meta( 'label' );
}
```

The rule checks direct `$this->meta()` calls with a matching constant string
inside the getter. It does not follow cycles through other methods or nested
closure bodies.

## Optional manifest-omission advice

Enable advisory checks explicitly:

```neon
includes:
    - vendor/pressgang-wp/phpstan/rules.neon
```

`pressgang.contextGetterOrphan` reports `get_*()` methods absent from a concrete
controller's effective manifest, including inherited and trait methods. Abstract
controllers and the lifecycle methods `get_context()` and
`get_template_candidates()` are excluded from this advice.

An omission is not proof of dead code: another getter, a lifecycle method or a
hook may call the method. The rule does not build a direct-call reachability
graph. Mark an intentional helper on its method PHPDoc:

```php
/**
 * Supplies a label to another getter rather than a standalone context key.
 *
 * @pressgang-context-helper
 * @return string
 */
protected function get_label(): string {
    return 'Latest';
}
```

The annotation suppresses only the orphan advisory. It does not suppress missing
methods or recursion. PHPStan has no separate warning severity: opting in makes
these findings affect its normal exit status. Review callers before removing a
getter; absence from a manifest does not itself cause a query to run.

## Handling findings and scope

Fix incorrect source or return contracts first. The extension preserves ordinary
PHPStan errors: it will not turn a base-class collection into a promised
subclass array, make a missing Timber property exist, or narrow a nullable
collection just because a controller uses a manifest.

Keep the framework baseline-free. In a child-theme migration, a baseline should
be an explicitly agreed inventory of existing debt, not a blanket ignore for
`pressgang.*` diagnostics. Review optional advisories separately from automatic
checks, and retain a way to inspect unbaselined findings.

| Question | Tool |
| --- | --- |
| Does a manifest name an existing getter? | PHPStan extension |
| What type does a custom model getter expose? | PHPStan extension |
| Does a getter recurse through its own metadata key? | PHPStan extension |
| Which namespace, snippets and merged config are active? | `wp capstan doctor` |
| Does a route render correctly in a real theme? | Capstan and [Shakedown](SHAKEDOWN.md) |

Config return-shape maps and Twig/template validation are outside this extension.
Runtime namespace resolution, installed packages and merged configuration need
a booted WordPress environment; static analysis should not guess those values.
