---
description: >-
  Deterministic, Laravel-style content seeding for WordPress — fluent
  builders, seeded fake data, idempotent re-runs, and fixtures derived
  straight from your ACF field groups.
---

# 🍪 Muster

Aboard ship, a muster assembles the crew and counts every hand. In your project, Muster assembles **content**: posts, pages, terms, users, menus, media and options, created through fluent builders with realistic fake data — the same content, byte for byte, every time you run it.

## 🗺️ The naming, translated

The names are nautical (like the rest of the fleet), but the concepts are the seeders and factories you know from other frameworks:

| Muster | Laravel | Rails / elsewhere | Role |
| --- | --- | --- | --- |
| `Muster` class + `run()` | `DatabaseSeeder` + `run()` | `seeds.rb` | The orchestrator: one place describing the content a site should have |
| `Pattern` + `count()` + `build()` | `Model::factory()->count(5)->create()` | FactoryBot's `create_list` | Repeatable batch creation from a recipe |
| `Victuals` (a ship's provisions) | `$this->faker` | `Faker` gem | Seeded fake data — headlines, emails, dates |
| Builders (`post()`, `term()`, `menu()`…) | Factory definitions / Eloquent creates | Factory definitions | Explicit, typed creation of one kind of thing |
| Refs (`PostRef`, `TermRef`…) | The returned model instance | Ditto | A handle for wiring relationships (parents, menu items, featured images) |
| `populated()` / `minimal()` ACF variants | Factory *states* | FactoryBot traits | Named variations of the same content shape |

Two WordPress-flavoured differences from the Laravel model: builders **upsert on natural keys** rather than always inserting (a Muster describes desired state, so re-runs converge), and there's no ORM underneath — every builder calls real WordPress APIs (`wp_insert_post()`, `wp_set_object_terms()`, ACF's `update_field()`).

{% hint style="success" %}
**The promise is determinism.** Same seed, same content, on every machine, forever. That's what makes seeded content usable for visual regression, stable selectors, and reproducible bug reports — and it's why [Shakedown](SHAKEDOWN.md) uses Muster to build its sandbox fixtures.
{% endhint %}

{% hint style="warning" %}
Seeding **writes to the database it's pointed at**. Use it on throwaway environments (Shakedown's sandbox does this automatically) or deliberately on a dev site you're happy to fill. Upserts make re-runs safe; they don't make the first run reversible.
{% endhint %}

## 📦 Install

Muster isn't on Packagist yet — pull it from GitHub in your theme's `composer.json`:

{% code title="composer.json" %}
```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/pressgang-wp/pressgang-muster" }
  ],
  "require-dev": {
    "pressgang-wp/muster": "dev-main"
  }
}
```
{% endcode %}

**Requirements:** PHP 8.3+. The builders call live WordPress functions, so seeding needs a booted site (WP-CLI provides that); value generation alone runs anywhere.

## 🧠 Mental model

Five pieces, each doing one job:

* **`Muster`** — your orchestrator. Extend it, implement `run()`, compose everything below.
* **Builders** — fluent, explicit creators for posts/pages (and any CPT), terms, users, options, **menus**, and **attachments**, plus a `truncate()` reset. Every builder upserts on a natural key (post type + slug, taxonomy + slug, login…), so re-running a Muster converges instead of duplicating.
* **`Victuals`** — a curated [Faker](https://fakerphp.org/) wrapper with UK-leaning defaults: `headline()`, `sentence()`, `content()`, `email()`, `ukPostcode()`, `date()`…
* **Patterns** — repeatable batch runs: `->pattern('events')->count(5)->build(fn ($i) => …)`, with per-pattern seed overrides.
* **Refs** — every `save()` returns an immutable reference (`PostRef`, `TermRef`, `MenuRef`…) you can feed into later builders (parents, menu items, featured images).

## ✍️ A Muster in practice

{% code title="muster/DemoMuster.php" %}
```php
use PressGang\Muster\Muster;

final class DemoMuster extends Muster
{
    public function run(): void
    {
        $about = $this->page()
            ->title('About us')
            ->slug('about-us')
            ->status('publish')
            ->date('2026-01-01 09:00:00')          // pin dates: rendered dates must not drift
            ->content($this->victuals()->paragraphs(3))
            ->save();

        $hero = $this->attachment('about-hero')
            ->placeholder(1200, 800)                // deterministic generated image
            ->alt('Our team at work')
            ->featuredOn($about)
            ->save();

        $this->menu('Main Menu')
            ->postItem($about, 'About')
            ->link('Contact', '/contact/')
            ->location('main-menu')
            ->save();

        $this->pattern('events')->seed(1201)->count(5)->build(
            fn (int $i) => $this->post('event')
                ->title($this->victuals()->headline())
                ->slug('event-' . $i)
                ->status('publish')
        );
    }
}
```
{% endcode %}

Run it through [Capstan](CAPSTAN.md)'s command surface:

{% code title="Terminal" %}
```bash
wp capstan muster App\\Muster\\DemoMuster --seed=1234           # deterministic run
wp capstan muster App\\Muster\\DemoMuster --dry-run             # log intent, write nothing
wp capstan muster App\\Muster\\DemoMuster --only=events         # just one pattern
```
{% endcode %}

## 🌱 Development seeding

You don't have to write your SiteMuster from scratch — [Capstan](CAPSTAN.md) can scaffold one from the theme's own shape (post types, taxonomies, page templates, menu locations):

{% code title="Terminal" %}
```bash
wp capstan make muster --force   # scaffolds src/Muster/SiteMuster.php (preview without --force)
wp capstan seed                  # runs it — Laravel's db:seed, for WordPress
wp capstan seed --fresh          # clean-slate reset first, then seed
```
{% endcode %}

The generated file seeds five of each post type (ACF values derived via `acfFor()`), three terms per taxonomy, a page per registered page template, and a menu per location — all with pinned dates and idempotent slugs, so re-seeding converges. It's generated **once** and never overwritten: it's your file; rename fixtures and add domain content freely.

{% hint style="warning" %}
`wp capstan seed` refuses outright when `WP_ENVIRONMENT_TYPE` is `production` — no override flag exists, by design. Seeding is for development sites and disposable sandboxes.
{% endhint %}

## 🧬 ACF fixtures, derived

The standout feature: Muster can generate fixture values **from your `acf-json/` exports** instead of you writing them. `AcfJson` reads each field group and its location rules; `AcfValueGenerator` produces `update_field()`-ready values for ~25 field types — recursing through repeaters, groups, and flexible content (one row per layout, so every layout renders at least once).

Two variants per group drive **state testing**:

* `populated()` — every generatable field filled.
* `minimal()` — required fields only: the sparsest content an editor can legally publish, which is exactly where empty-link and missing-image template bugs live. The required-only rule applies at every depth, so a required repeater still recurses with only its required sub-fields.

The generator is **pure**: media and relational fields come from injected providers (Shakedown wires these to `AttachmentBuilder` placeholders and stub posts/terms), so it unit-tests without WordPress. Applied values go through the `LiveAcfAdapter`, a thin pass-through to [`update_field()`](https://www.advancedcustomfields.com/resources/update_field/) — top-level values keyed by field key, sub-values by name, as ACF expects. Options-page groups seed globally so site chrome (header/footer) renders fully.

Preview what any theme's groups would generate — no WordPress needed:

{% code title="Terminal" %}
```bash
php bin/demo-acf-values.php /path/to/theme/acf-json 42
```
{% endcode %}

## 🎯 Design decisions

* **Why fake data, not a content dump?** Dumps carry privacy risk, drift from your schema, and can't exercise states nobody has authored yet. WordPress's generic [theme-test-data](https://github.com/WordPress/theme-test-data) knows nothing about *your* field groups. Derived + faked content is schema-true, shareable, and covers the states that *can* exist rather than the ones that happen to.
* **Why Faker, seeded?** [FakerPHP](https://fakerphp.org/) is the ecosystem standard for realistic fake data; a fixed seed makes it deterministic. One caveat worth knowing: Faker's `seed()` drives PHP's *global* `mt_rand` stream, so determinism holds per **sequence** of draws — Muster's flows (one context, sequential draws) stay inside that contract, but two coexisting same-seed instances interleaving draws would diverge.
* **Why idempotent upserts?** Natural-key upserts (slug, login, option name) mean a Muster is a *description of desired state*, not a script that piles up duplicates — re-run it freely, in CI or locally, and it converges.
* **Why builders over raw `wp_insert_post()`?** Explicit, typed, discoverable — and one place to encode the fiddly WordPress details (e.g. `post_date` changes on updates silently require `edit_date`; the builder handles it so you never learn that the hard way).
* **Why an adapter for ACF?** `update_field()` is ACF's public write API and handles repeaters/groups natively — but Muster shouldn't hard-depend on ACF. The adapter seam means no-ACF sites no-op cleanly, and tests can capture writes without the plugin.

## 🛳️ With the fleet

* **[Shakedown](SHAKEDOWN.md)** seeds Muster fixtures into its disposable sandboxes — never a real database — giving every ACF field group a populated and a minimal test page automatically.
* **[Capstan](CAPSTAN.md)** hosts the CLI entry point (`wp capstan muster …`).

## 🧭 Roadmap notes

Comments, WooCommerce products, and Gutenberg block content are on the [roadmap](https://github.com/pressgang-wp/pressgang-muster/blob/main/ROADMAP.md); `page_type` / `post_template` / `nav_menu_item` ACF locations aren't seedable yet.
