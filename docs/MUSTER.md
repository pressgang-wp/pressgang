---
description: >-
  WordPress-native deterministic content provisioning with stable ownership,
  fluent builders, seeded fake data, and ACF-derived development fixtures.
---

# 🍪 Muster

Aboard ship, a muster assembles the crew and accounts for every hand. In a
WordPress project, [Muster](https://github.com/pressgang-wp/pressgang-muster)
assembles content: posts, pages, terms, users, options, menus, and media created
through WordPress and plugin APIs.

Muster is a **WordPress-native toolkit for deterministic content provisioning
and development fixtures**. It belongs to the PressGang ecosystem, but it does
not require Models, introduce an ORM, or map application objects directly onto
`wp_posts` and `wp_postmeta`.

{% hint style="info" %}
Muster takes useful inspiration from seeders and factories in Laravel, Rails,
and other frameworks. It adapts those ideas to WordPress rather than porting
their persistence models.
{% endhint %}

## 📦 Installation

Muster is published on
[Packagist](https://packagist.org/packages/pressgang-wp/muster). Install it in
the child theme that owns the fixtures:

{% code title="Terminal" %}
```bash
composer require --dev pressgang-wp/muster
```
{% endcode %}

Installing it as a development dependency is recommended for local setup, CI,
and disposable test environments. If a controlled non-production runtime needs
Muster after a `composer install --no-dev`, install it as a regular dependency
instead.

**Requirements:** PHP 8.3+, FakerPHP 1.24+, and a loaded WordPress runtime when
resources are persisted. WP-CLI is required for the `wp capstan` commands.

## 🧠 Mental model

| Concept | Responsibility |
| --- | --- |
| `Muster` | The orchestration entry point. A subclass implements `run()` and describes one provisioning flow. |
| Builders | Collect intent for one WordPress resource and write it through the relevant WordPress API on `save()`. |
| Logical keys | Stable identity within one concrete Muster class, independent of mutable WordPress locators such as slugs. |
| `Victuals` | A curated Faker wrapper for seeded headlines, content, names, addresses, dates, and other fixture values. |
| `Pattern` | Repeats a post-builder recipe a declared number of times with an optional pattern seed. |
| Refs | Immutable handles such as `PostRef`, `TermRef`, and `MenuRef` used to connect saved resources. |
| `RunReport` | Ordered `create`, `update`, `keep`, `prune`, and `conflict` results for one plan or apply pass. |
| ACF generation | Reads `acf-json` field definitions and produces minimal or populated field values for coverage fixtures. |

Patterns currently require a `PostBuilder`. Generic repeated declarations for
terms, users, attachments, and other resources remain future work.

## ✍️ A Muster in practice

{% code title="src/Muster/SiteMuster.php" %}
```php
<?php

namespace App\Muster;

use PressGang\Muster\Muster;

final class SiteMuster extends Muster
{
    public function run(): void
    {
        $about = $this->page()
            ->key('page:about')
            ->title('About us')
            ->slug('about-us')
            ->status('publish')
            ->date('2026-01-01 09:00:00')
            ->content($this->victuals()->paragraphs(3))
            ->save();

        $this->attachment('about-hero')
            ->key('attachment:about-hero')
            ->placeholder(1200, 800, 'About us')
            ->alt('Our team at work')
            ->featuredOn($about)
            ->save();

        $this->menu('Main Menu')
            ->key('menu:main')
            ->postItem($about, 'About')
            ->link('Contact', '/contact/')
            ->location('main-menu')
            ->save();

        $this->pattern('events')
            ->seed(1201)
            ->count(5)
            ->build(
                fn (int $i) => $this->post('event')
                    ->key('event:' . $i)
                    ->title($this->victuals()->headline())
                    ->slug('event-' . $i)
                    ->status('publish')
                    ->date('2026-01-01 09:00:00')
            );
    }
}
```
{% endcode %}

Every write goes through WordPress-native functions such as `wp_insert_post()`,
`wp_update_post()`, `wp_insert_term()`, `wp_update_user()`, and
`wp_update_nav_menu_item()`. ACF values use its public `update_field()` API.

## 🔁 Persistence semantics

Every builder created through a Muster requires an explicit `key()`. The
concrete Muster class and logical key are stable identity; the native
WordPress locator is how Muster discovers or updates the current object.

Posts, terms, and users use **merge-upsert** semantics:

1. Resolve an owned resource by Muster class and logical key.
2. Check the current WordPress locator for collisions.
3. Create it when it does not exist.
4. When it exists, update only fields explicitly supplied to the builder.
5. Preserve omitted WordPress fields; passing an empty value explicitly clears
   a field.

| Resource | Current locator |
| --- | --- |
| Post, page, or CPT | Post type + slug |
| Term | Taxonomy + slug |
| User | Login |
| Option | Option name |
| Attachment | Attachment slug |
| Menu | Menu name |

The other builders have deliberately different current behavior:

* **Options** use WordPress's option upsert behavior.
* **Attachments** create a file and attachment once, then reuse an existing
  attachment with the same slug. Changing the declared source does not currently
  regenerate an existing file.
* **Menus** treat the declaration as authoritative: every existing item in the
  named menu is deleted and the declared items are recreated in order.
* **`truncate()`** permanently deletes every post of the selected type or every
  term in the selected taxonomy.

{% hint style="warning" %}
Natural-key lookup does not prove ownership. If a matching resource exists but
is not registered to this Muster key, saving fails. Call `adopt()` only when the
declaration is intentionally taking responsibility for that existing object.
Adoption never steals a resource already owned by another Muster or key.
{% endhint %}

The persistence contract distinguishes three modes for that future lifecycle:
`ensure` (create only), `merge` (update supplied fields), and `replace`
(complete authoritative state). Merge is the current default; ensure and
replace are not yet public builder modes.

### Inspectable plan and apply

Every CLI command runs the Muster in a read-only planning context first. The
plan resolves current WordPress resources and reports one of five operations:

| Operation | Meaning |
| --- | --- |
| `create` | No owned or adopted resource currently exists. |
| `update` | The owned declaration differs or has authoritative side effects. |
| `keep` | Comparable declared state already matches WordPress. |
| `prune` | An owned or explicitly truncated resource will be deleted. |
| `conflict` | Ownership or locator safety prevents application. |

Without `--dry-run`, Muster prints the plan and then performs a second pass that
re-resolves WordPress state and applies the declarations. Planning conflicts
stop before any application writes. WordPress has no transaction spanning all
of its resource APIs, so the application pass validates again rather than
treating the earlier reads as a lock.

{% hint style="warning" %}
A normal CLI application calls `run()` twice: once to plan and once to apply.
Keep `run()` declarative. Do not send email, call remote APIs, or perform writes
outside Muster builders.
{% endhint %}

Core post, term, user, and option fields can be proven unchanged and reported
as `keep`. ACF/meta/taxonomy payloads and authoritative menu rebuilds are
conservatively reported as updates until their adapters expose comparable read
contracts. Programmatic integrations can inspect
`$context->report()->operations()`, `summary()`, or `toArray()`.

### Ownership, adoption, and cleanup

Muster stores ownership records in the non-autoloaded
`pressgang_muster_registry` WordPress option. Records contain the concrete
Muster class, logical key, resource kind, WordPress ID, subtype, and current
locator. WordPress remains the source of truth for the resource itself.

```php
$this->page()
    ->key('page:about')
    ->adopt() // required only to claim a pre-existing unowned page
    ->title('About us')
    ->slug('about-us')
    ->save();

$this->resetOwned();
$this->pruneOwned(); // after a complete run, remove keys not touched this run
$this->pruneOwned(['page:seasonal']); // optionally retain conditional keys too
```

`resetOwned()` deletes every registered resource owned by that concrete Muster
class. `pruneOwned()` automatically keeps keys saved in the current run,
including reserved `acf:*` support keys, and deletes stale owned resources. Its
optional array means “also keep.” Never prune after a partial `--only` run.
Both operations leave editor-created and other unowned WordPress content alone.

## 🌱 Conventional development seed

[Capstan](CAPSTAN.md) can inspect the active theme and scaffold a starting
`SiteMuster` from its registered post types, taxonomies, page templates, menu
locations, and ACF JSON:

{% code title="Terminal" %}
```bash
wp capstan make muster          # preview src/Muster/SiteMuster.php
wp capstan make muster --force  # write it once
wp capstan seed --seed=1234     # run the conventional SiteMuster
wp capstan seed --dry-run       # resolve and report the plan without writes
wp capstan seed --fresh         # reset this Muster's owned resources, then run
wp capstan seed --format=json   # machine-readable plan and apply reports
```
{% endcode %}

The generated file is a starting point owned by the child theme and is never
overwritten by the scaffold command. Edit its sample counts, names, content,
relationships, and logical keys to fit the project.

`wp capstan seed` refuses to run when `WP_ENVIRONMENT_TYPE` is `production`.
There is no override flag. Code can also run a named Muster through the lower
level command:

{% code title="Terminal" %}
```bash
wp capstan muster App\\Muster\\DemoMuster --seed=1234
wp capstan muster App\\Muster\\DemoMuster --dry-run
wp capstan muster App\\Muster\\DemoMuster --only=events
wp capstan muster App\\Muster\\DemoMuster --format=json
```
{% endcode %}

`--dry-run` performs the full read-only planning pass and stops before
application. `--format=json` suppresses human log lines and emits one object
containing `status`, ordered `operations`, and per-action `summary` values for
the plan and optional apply pass. It is suitable for CI checks and tooling.

`--only` currently filters named **Patterns**, not direct builder calls.
Combining it with `--fresh` intentionally clears all resources owned by the
Muster and then rebuilds only the selected Patterns plus direct declarations.

{% hint style="info" %}
`--fresh` is ownership-aware and requires no custom `fresh()` method. The broad
`truncate()` builder remains available for deliberately disposable databases,
but Capstan's conventional fresh seed does not use it.
{% endhint %}

## 🎲 Determinism

An explicit seed gives Faker-backed values a repeatable sequence:

```bash
wp capstan seed --seed=1978
```

A Pattern seed overrides the run seed for that Pattern. Calls within a Pattern
share one scoped `Victuals` instance, so the sequence is stable when the seed,
call order, locale, and inputs are stable.

There are two important limits:

* Faker's seeding uses PHP's global `mt_rand` stream. Interleaving independently
  seeded Faker instances can change their sequences.
* Relative date boundaries such as `dateBetween('+1 week', '+6 months')` depend
  on the current clock. Pin dates explicitly for visual fixtures. A separate
  deterministic fixture epoch is planned.

[Shakedown](SHAKEDOWN.md) supplies a fixed seed and pins published dates inside
its disposable sandbox, which keeps its generated visual fixtures stable.

## 🧬 ACF-derived coverage fixtures

`acf-json` is the machine-readable description of a theme's editorial surface.
Muster can use it to generate values instead of duplicating every field
definition in seed code:

```php
$this->post('event')
    ->key('event:example')
    ->title('Example event')
    ->slug('example-event')
    ->acf($this->acfFor('event'))
    ->save();
```

`acfFor()` supports two variants:

* `populated` fills every generatable field.
* `minimal` fills required fields only, including required nested sub-fields.

The generator handles common scalar fields plus groups, repeaters, flexible
content, galleries, and relational fields. Relational and media fields need
real WordPress IDs, so `Muster::acfFor()` may create deterministic supporting
attachments, posts, or terms through its providers. Treat it as provisioning,
not as a side-effect-free value lookup. Those supporting resources receive
reserved `acf:*` logical keys and are owned by the calling Muster.

When ACF is active, the CLI wires `LiveAcfAdapter` and writes through
`update_field()`. When ACF is unavailable, ACF payloads are not persisted.

This capability is especially valuable to [Shakedown](SHAKEDOWN.md): each ACF
field group receives populated and minimal fixtures in an isolated WordPress
sandbox, exercising both rich content and sparse-but-valid editorial states.

## 🧰 Builder reference

| Entry point | Creates or updates |
| --- | --- |
| `$this->post('event')->key('event:1')` | Posts and custom post types |
| `$this->page()->key('page:about')` | Pages |
| `$this->term('event_type')->key('event-type:talk')` | Taxonomy terms |
| `$this->user()->key('user:editor')` | WordPress users |
| `$this->option('name')->key('option:name')` | WordPress options |
| `$this->attachment('hero')->key('attachment:hero')` | Media attachments and deterministic placeholders |
| `$this->menu('Main Menu')->key('menu:main')` | Navigation menus, items, nesting, and locations |
| `$this->resetOwned()` | Every resource owned by this concrete Muster |
| `$this->pruneOwned([...])` | Stale owned resources not touched or additionally retained |
| `$this->truncate()` | Immediate destructive post-type or taxonomy reset |

Refs returned by `save()` use real WordPress IDs without exposing database-table
details. They can be passed to parents, menu items, attachment relationships,
and featured-image assignments.

## 🛳️ With the fleet

* **[Capstan](CAPSTAN.md)** scaffolds the theme's `SiteMuster` and hosts the
  `wp capstan seed` and `wp capstan muster` command surface.
* **[Shakedown](SHAKEDOWN.md)** uses Muster for ACF-derived state fixtures in a
  disposable WordPress sandbox.
* **[Bosun](BOSUN.md)** can brief coding agents on the installed PressGang tools
  and project conventions.

## 🧭 Current roadmap

Logical ownership, collision detection, adoption, owned reset/pruning, and the
structured plan/apply lifecycle are implemented. The next priority is named
declaration groups, followed by a deterministic fixture clock and a real
WordPress integration suite.

See the maintained
[Muster roadmap](https://github.com/pressgang-wp/pressgang-muster/blob/main/ROADMAP.md)
for current status.
