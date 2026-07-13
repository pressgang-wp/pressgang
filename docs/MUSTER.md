---
description: >-
  WordPress-native deterministic content provisioning and development fixtures,
  with fluent builders, seeded fake data, and ACF-derived coverage states.
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
| `Victuals` | A curated Faker wrapper for seeded headlines, content, names, addresses, dates, and other fixture values. |
| `Pattern` | Repeats a post-builder recipe a declared number of times with an optional pattern seed. |
| Refs | Immutable handles such as `PostRef`, `TermRef`, and `MenuRef` used to connect saved resources. |
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
            ->title('About us')
            ->slug('about-us')
            ->status('publish')
            ->date('2026-01-01 09:00:00')
            ->content($this->victuals()->paragraphs(3))
            ->save();

        $this->attachment('about-hero')
            ->placeholder(1200, 800, 'About us')
            ->alt('Our team at work')
            ->featuredOn($about)
            ->save();

        $this->menu('Main Menu')
            ->postItem($about, 'About')
            ->link('Contact', '/contact/')
            ->location('main-menu')
            ->save();

        $this->pattern('events')
            ->seed(1201)
            ->count(5)
            ->build(
                fn (int $i) => $this->post('event')
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

Posts, terms, and users currently use **merge-upsert** semantics:

1. Find an existing resource by its documented natural key.
2. Create it when it does not exist.
3. When it exists, update only fields explicitly supplied to the builder.
4. Preserve omitted WordPress fields; passing an empty value explicitly clears
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
Natural-key lookup does not yet prove that Muster owns the matching resource.
An existing page, user, term, attachment, or menu may have been created by an
editor or another tool. Ownership metadata, collision detection, owned-only
reset, and stale-resource pruning are the next reconciliation milestone.
{% endhint %}

The persistence contract distinguishes three modes for that future lifecycle:
`ensure` (create only), `merge` (update supplied fields), and `replace`
(complete authoritative state). Merge is the current default; ensure and
replace are not yet public builder modes.

## 🌱 Conventional development seed

[Capstan](CAPSTAN.md) can inspect the active theme and scaffold a starting
`SiteMuster` from its registered post types, taxonomies, page templates, menu
locations, and ACF JSON:

{% code title="Terminal" %}
```bash
wp capstan make muster          # preview src/Muster/SiteMuster.php
wp capstan make muster --force  # write it once
wp capstan seed --seed=1234     # run the conventional SiteMuster
wp capstan seed --dry-run       # show current intent without writes
wp capstan seed --fresh         # call SiteMuster::fresh(), then run
```
{% endcode %}

The generated file is a starting point owned by the child theme and is never
overwritten by the scaffold command. Edit its sample counts, names, content,
relationships, and reset policy to fit the project.

`wp capstan seed` refuses to run when `WP_ENVIRONMENT_TYPE` is `production`.
There is no override flag. Code can also run a named Muster through the lower
level command:

{% code title="Terminal" %}
```bash
wp capstan muster App\\Muster\\DemoMuster --seed=1234
wp capstan muster App\\Muster\\DemoMuster --dry-run
wp capstan muster App\\Muster\\DemoMuster --only=events
```
{% endcode %}

`--only` currently filters named **Patterns**, not direct builder calls. Avoid
combining `--fresh` and `--only` unless the Muster's `fresh()` implementation is
explicitly designed for a partial run.

{% hint style="danger" %}
`--fresh` is not an ownership-aware rollback. The scaffolded `fresh()` uses
`truncate()`, which can remove non-Muster content of the configured types. Use
it only on disposable environments or a development database whose contents
you are prepared to lose.
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
not as a side-effect-free value lookup.

When ACF is active, the CLI wires `LiveAcfAdapter` and writes through
`update_field()`. When ACF is unavailable, ACF payloads are not persisted.

This capability is especially valuable to [Shakedown](SHAKEDOWN.md): each ACF
field group receives populated and minimal fixtures in an isolated WordPress
sandbox, exercising both rich content and sparse-but-valid editorial states.

## 🧰 Builder reference

| Entry point | Creates or updates |
| --- | --- |
| `$this->post('event')` | Posts and custom post types |
| `$this->page()` | Pages |
| `$this->term('event_type')` | Taxonomy terms |
| `$this->user()` | WordPress users |
| `$this->option('name')` | WordPress options |
| `$this->attachment('hero')` | Media attachments and deterministic placeholders |
| `$this->menu('Main Menu')` | Navigation menus, items, nesting, and locations |
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

The next priority is trustworthy reconciliation: stable logical keys, ownership
metadata, collision detection, an inspectable plan/apply lifecycle, and
owned-only reset and pruning. Generic Patterns and additional builders come
after those safety semantics.

See the maintained
[Muster roadmap](https://github.com/pressgang-wp/pressgang-muster/blob/main/ROADMAP.md)
for current status.
