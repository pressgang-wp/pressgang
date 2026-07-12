---
description: >-
  Why Shakedown is built the way it is — and how each layer works, with the
  third-party tools it stands on and where their documentation lives.
---

# 🧭 Shakedown: Design & Internals

The [Shakedown guide](SHAKEDOWN.md) tells you what to run. This page explains **why it's built this way** and **how each layer works** — useful when you're extending it, debugging it, or deciding whether to trust it.

## 🤔 Design decisions

### Why derived, not authored

Authored e2e suites die of maintenance: someone lists the URLs, writes the assertions, and forgets to update both. PressGang themes are different — the route surface is *declared* in `config/`, so the suite can be generated from the theme itself and never drifts. Add a CPT, gain its tests. Authored specs are reserved for the things derivation can't know: user journeys. This is the central bet; everything else follows from it.

### Why Playwright

Playwright is what WordPress core itself uses — all core and Gutenberg browser tests migrated from Puppeteer in 2023 ([announcement](https://make.wordpress.org/core/2023/10/16/wordpress-core-is-now-using-playwright-for-all-browser-based-tests/)). Practically it gives us, in one dependency: auto-waiting locators, an HTTP request client (pass 00 needs no browser), parallel workers, trace capture for post-mortem debugging, and first-party visual comparison — plus official axe-core bindings. The strongest alternative, [wp-browser/Codeception](https://wpbrowser.wptestkit.dev/), is excellent for PHP-side integration tests but its browser layer (WebDriver) is a generation behind, and a browser harness belongs in Node where the browser tooling lives. Docs: [playwright.dev](https://playwright.dev/docs/intro).

### Why an mu-plugin for the observer

The observer must run on **every request**, load **before plugins**, and require **no database state** — activation is a DB row, and Shakedown never writes to a real database. `mu-plugins/` is WordPress's canonical mechanism for exactly this: always loaded, can't be deactivated, and Composer-native (`"type": "wordpress-muplugin"` routes packages there via installer-paths). The alternatives are worse: drop-ins (`db.php`, `object-cache.php`) are single-occupancy and fought over by caching plugins; editing `wp-config.php` means mutating a file we don't own. Today the observer is installed **only into sandboxes**, assembled fresh each run.

### Why seed and fake — hence Muster

Three reasons real content can't be the fixture:

1. **Determinism.** Visual snapshots and stable selectors need content that is byte-identical across runs and machines. [Muster](https://github.com/pressgang-wp/pressgang-muster) seeds via a seeded [Faker](https://fakerphp.org/) — same seed, same content, forever — and pins post dates so rendered dates never drift.
2. **Denominators.** Real content only exercises the states editors happen to have created. Fixtures derived from `acf-json` exercise the states that *can exist* — including the all-important **minimal state** (required fields only), where empty-link and missing-image bugs live. Real content found one such bug on BHP by luck; derivation finds them systematically.
3. **The hard rule.** Nothing ever writes to a real site's database. Seeding is therefore only possible in an environment that is disposable *by construction* — which is why Muster and the sandbox arrived together.

Muster follows Laravel's seeder model (fluent builders, idempotent upserts, `--seed=N`) because it's the proven shape for this problem.

### Why the sandbox is SQLite

The [SQLite Database Integration plugin](https://wordpress.org/plugins/sqlite-database-integration/) (the WordPress Performance team's own project, with a real MySQL-parser driver since 2025) lets a genuine PHP WordPress run with a single database *file* — no MySQL server, no Docker, nothing shared. The sandbox symlinks your **code** read-only and owns its **state** (config, uploads, database) in a temp dir: Laravel's in-memory test database, translated to WordPress. Isolation isn't assumed — every boot queries a witness endpoint and refuses to test unless `ABSPATH`, the content dir, and the database all resolve inside the temp directory. (Hard-won: PHP resolves `__DIR__` through symlinks, so entry PHP files are *copied* — a symlinked `wp-load.php` would load the real site's config.)

### Why CI

A suite that only runs on one laptop rots; a gate on every push makes silent regressions unmergeable. The sandbox is what makes CI honest *and* cheap: because it needs only the theme repo (core downloaded bare, parent + plugins provisioned by the theme's own Composer installer-paths, fixtures derived), there's no database dump, no site bundle, no Docker — a run costs pennies on GitHub's Linux runners and finishes in minutes.

***

## ⚙️ How it works, layer by layer

### Route derivation — Capstan

When [Capstan](CAPSTAN.md) is installed, Shakedown shells out to it:

{% code title="Terminal" %}
```bash
wp capstan matrix --resolve --format=json --samples=2 --search=research
```
{% endcode %}

```json
{ "routes": [ {
    "url": "https://mysite.test/events/",
    "kind": "archive:event",
    "expect": 200,
    "template": "dispatch.php",
    "controller": "MySite\\Controllers\\EventsController"
} ] }
```

`--resolve` replays each URL through Capstan's request simulator to attach the **oracle**. In testing, an *oracle* is whatever authoritatively tells you what the correct answer **should** be, so a test can judge what actually happened. Without one, a test can only check generic properties ("returned 200, no errors"); with one, it checks *intent*. Here the oracle is PressGang's own routing logic, replayed without rendering: for each URL it declares the template and controller the framework means to use — and at runtime the observer reports what really rendered, so the two can be compared. A page that quietly falls back to `index.php` still returns a healthy 200; only the oracle comparison catches it.

Without Capstan, a bundled `matrix.php` derives the same route families via `wp eval-file`, minus the oracle. Before any derivation, `wp capstan doctor --format=json` runs as a pre-flight — 11 deterministic config checks; failures abort the run before a browser launches.

### Runtime observation — the observer mu-plugin

Inside the sandbox, every response carries headers describing what actually happened:

```
X-Shakedown-Template: dispatch.php
X-Shakedown-Controller: events_controller
X-Shakedown-Php-Issues: 0
```

Pass 00 compares the first two against the oracle (so a route silently falling back to `index.php` is a hard failure), and fails any route whose PHP-issue count is non-zero — notices are counted by an error handler even when display and logging are off. Output is buffered for the whole request so headers can still be written at shutdown.

### The passes — Playwright

Shakedown runs Playwright with a packaged config; your theme directory is the *workspace* (reports, matrix, and baselines land there; a `tests/e2e/` dir joins the run as the journeys project). Pass 00 uses Playwright's [APIRequestContext](https://playwright.dev/docs/api-testing) (no browser — whole-site sweep in seconds); passes 01–03 drive Chromium. Failures retain a **trace** — open with `npx playwright show-trace <trace.zip>` for a time-travel replay ([trace viewer docs](https://playwright.dev/docs/trace-viewer)). The developer-grade HTML report lands in `playwright-report/` ([reporter docs](https://playwright.dev/docs/test-reporters)).

### Accessibility — axe-core

Pass 02 uses Deque's official [`@axe-core/playwright`](https://playwright.dev/docs/accessibility-testing):

```js
new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).exclude('iframe').analyze()
```

Output is a violations array — rule id, impact, offending nodes, and a `helpUrl` into [Deque University's rule reference](https://dequeuniversity.com/rules/axe/) explaining each fix. Shakedown's gate: `serious`/`critical` fail the route; `moderate`/`minor` print as advisories (promote them once the serious set is clean). Iframe *contents* are excluded — YouTube's player chrome is not your remediation surface.

### Visual regression — Playwright snapshots

Pass 03 is Playwright's built-in [`toHaveScreenshot`](https://playwright.dev/docs/test-snapshots): full-page, animations disabled, `<time>` elements masked, `maxDiffPixelRatio: 0.001`. Baselines are written to the theme's `tests/__screenshots__/{platform}/` — per-platform because font rendering differs between macOS and Linux, so local and CI baselines coexist. On a diff, `test-results/` contains expected/actual/diff images. Refresh intentionally with `npx shakedown sandbox --update-snapshots`; review the image changes in the PR like any other diff.

### The Trial Report — custom reporter

A small [Playwright reporter](https://playwright.dev/docs/test-reporters#custom-reporters) collects every result and writes two artifacts to `.shakedown/`:

* `run.json` — machine-readable: `{generated, target, results: [{pass, kind, url, status, error}]}` — the seed for future coverage tooling.
* `trial-report.html` + `screenshots/` — the client-readable handover: summary strip, a screenshot preview per route, a route × pass matrix, and failures in plain English. Self-contained folder; zip it, attach it, email it.

### Fixtures — Muster's seeding pipeline

`shakedown sandbox` runs a bundled PHP script via WP-CLI inside the sandbox: [`AcfJson`](https://github.com/pressgang-wp/pressgang-muster) reads the theme's `acf-json/`, extracts each group's seedable location (post type / page template / options page), and `AcfValueGenerator` produces values for ~25 ACF field types — recursing through repeaters, groups, and flexible content (one row per layout, so every layout renders at least once). Media fields get generated placeholder images (colour derived from the slug — deterministic), relational fields get stub posts/terms. Two variants per group: `populated` and `minimal`. Options-page groups seed once so the site chrome (header/footer) renders fully.

### The sandbox assembly — SQLite + wp server

The [SQLite Database Integration plugin](https://github.com/WordPress/sqlite-database-integration) is cached from wordpress.org and wired in via its `db.copy` drop-in template (`DB_DIR`/`DB_FILE` constants point at the temp dir). The site is served by [`wp server`](https://developer.wordpress.org/cli/commands/server/) — WP-CLI's built-in PHP server with a WordPress-aware router — on an OS-assigned ephemeral port. `wp core install`, theme activation, and permalink setup all run against the throwaway database.

### CI — the reusable workflow

The [workflow](https://github.com/pressgang-wp/pressgang-shakedown/blob/main/.github/workflows/shakedown.yml) checks the theme out *into* a WordPress-shaped tree, then: [`shivammathur/setup-php`](https://github.com/shivammathur/setup-php) (PHP + WP-CLI + Composer), `wp core download --skip-content`, `composer install` in the theme (parent + plugins land via installer-paths; ACF Pro credentials via the `COMPOSER_AUTH` secret — [Composer auth docs](https://getcomposer.org/doc/articles/authentication-for-private-packages.md)), Muster cloned for fixtures, Capstan installed for the oracle, then `npx shakedown sandbox`. Composer and [Playwright browser caches](https://playwright.dev/docs/ci#caching-browsers) keep warm runs fast; the Trial Report uploads as an artifact either way.
