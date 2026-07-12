---
description: >-
  End-to-end browser testing for PressGang themes with zero tests to write —
  the route matrix, fixtures, and checks are all derived from your theme.
---

# 🚢 Shakedown

A shakedown cruise is the sea trial of a new vessel: take her out, push every system, find what rattles before the passengers board. Shakedown does the same for your theme — and because PressGang themes declare their post types, taxonomies, templates and menus in `config/`, it can **derive the whole test suite from the site itself**. You write nothing to get started.

{% hint style="success" %}
**The one-liner:** run `npx shakedown` inside your theme and, in about a minute, every page your site serves has been checked for errors, broken assets, and accessibility problems — in a real browser.
{% endhint %}

## 🧰 Commands at a glance

Shakedown runs in one of two **modes** — keep the distinction in mind, everything below builds on it:

| Mode | Answers | Touches your database? |
| --- | --- | --- |
| **Attached** — your live local site | "Is my site healthy *right now*?" | Never writes — read-only GETs |
| **Sandbox** — a disposable throwaway WordPress | "Is my *theme* correct, independent of content?" | N/A — its own database, vaporised after |

| Command | Mode | What it does |
| --- | --- | --- |
| `npx shakedown` | Attached | Runs every pass against your local site |
| `npx shakedown matrix` | Attached | Prints the route matrix without running checks |
| `npx shakedown sandbox` | Sandbox | Spins up the throwaway WordPress, seeds fixtures, runs every pass |
| `npx shakedown sandbox --update-snapshots` | Sandbox | Re-mints visual regression baselines |
| `npx shakedown ui` | Either | Playwright's UI / watch mode, for fixing failures |
| `npx playwright show-report` | Either | Opens the last HTML report |

## 📦 Install

You need Node 20+, [WP-CLI](https://wp-cli.org/), and your site running locally (any server — Herd, Valet, DDEV, MAMP… it's just a URL). From inside your theme:

{% code title="Terminal" %}
```bash
npm i -D github:pressgang-wp/pressgang-shakedown
npx playwright install chromium   # once per machine
```
{% endcode %}

## ⚡ First trial

{% code title="Terminal" %}
```bash
npx shakedown
```
{% endcode %}

That's it — no config. Shakedown walks up from your theme to find `wp-config.php`, asks WP-CLI for the site URL, enumerates every route, and checks them all. Want to see the map before sailing?

{% code title="Terminal" %}
```bash
npx shakedown matrix
```
{% endcode %}

```
⚓ 54 routes for https://mysite.test (via capstan)
  [200] home            https://mysite.test/
  [200] archive:event   https://mysite.test/events/
  [200] single:event    https://mysite.test/events/spring-fair/
  [200] term:category   https://mysite.test/news/category/research/
  ...
```

The matrix covers your front page, every post type's archive plus sample singles, taxonomy term pages, every page using a registered page template, internal menu targets, a search probe, and a 404 probe. Add a post type to `config/custom-post-types.php` and the next run covers it automatically. 🗺️

## 🧪 What gets checked

| Pass | Checks |
| --- | --- |
| **00 · Availability** | Right HTTP status · no PHP/Twig error output · a `<title>` present. HTTP-only, so it sweeps the whole site in seconds. |
| **01 · Integrity** | Real Chromium render: no JS exceptions, console errors, failed requests, or broken images. |
| **02 · Accessibility** | axe-core against WCAG 2.1 A/AA. Serious/critical violations fail; minor ones report as advisory. |
| **03 · Visual** | Full-page screenshots against baselines committed in your theme. Skips politely until baselines exist. |

When something fails you get the exact URL, what was expected, and a Playwright trace to replay step-by-step. `npx shakedown ui` gives you watch mode while you fix it; `npx playwright show-report` browses the last run.

{% hint style="info" %}
Real story: on its very first outing, the two commands above found a site-breaking Twig fatal across an entire section of an unlaunched site — within ninety seconds of `npm install`. That's the pitch.
{% endhint %}

## 🏝️ The sandbox

Attached mode tests your site *as it is* — real content, full plugin stack, strictly **read-only**. The sandbox answers a different question: *is my theme correct?*

{% code title="Terminal" %}
```bash
npx shakedown sandbox
```
{% endcode %}

This assembles a **throwaway WordPress** in a temp directory: your code symlinked read-only, its own fresh SQLite database, its own uploads — think Laravel's in-memory test database, for WordPress. Then it seeds **state fixtures** derived from your ACF field groups via [Muster](MUSTER.md): for every group, one page/post with *every field populated* and one with *only required fields* — the sparsest content an editor can legally publish, which is exactly where empty-link and missing-image bugs live. Runs all passes, then vaporises.

```
⚓ sandbox up at http://127.0.0.1:54223 (isolation verified)
⚓ seeded 20 ACF state fixtures via Muster
⚓ capstan doctor: 11 checks, 0 failures, 0 warnings
⚓ 22 routes (via capstan)
113 passed (18s)
```

{% hint style="warning" %}
**Your database is never touched.** Attached mode only ever GETs pages. Seeding happens exclusively in the sandbox, and every boot runs an isolation check that *proves* the served WordPress lives in the temp directory before any test traffic flows — if it can't prove it, it refuses to run.
{% endhint %}

Plugins are **allowlisted** in the sandbox (default: none — ACF loads via mu-plugins). The sandbox tests your theme, not your plugin stack; a run that passes in the sandbox but fails attached tells you a plugin is the culprit.

## 📸 Visual baselines

Once your passes are green, mint the screenshots:

{% code title="Terminal" %}
```bash
npx shakedown sandbox --update-snapshots
```
{% endcode %}

Baselines land in your theme at `tests/__screenshots__/` (per-platform) — **commit them**. Because fixtures are seeded deterministically and dates are pinned, snapshots are byte-stable across runs: a future diff means the *theme* changed, not the content.

## 📋 The Trial Report

Every run writes `.shakedown/trial-report.html` — a self-contained, client-readable page: summary numbers, a screenshot preview per route, a route × pass matrix, and failures in plain English (no stack traces). Attach it to a PR, or send it with a handover. The developer-grade report with traces lives separately in `playwright-report/`.

## ⚙️ Configuration

None required. An optional `shakedown.config.json` in the theme handles the exceptions:

{% code title="shakedown.config.json" %}
```json
{
  "searchTerm": "research",
  "sandbox": {
    "plugins": ["contact-form-7"],
    "map": { "assets": "patterns/public/assets" }
  }
}
```
{% endcode %}

* `searchTerm` — a word that actually appears in your content, for the search probe.
* `sandbox.plugins` — plugins the sandbox should activate (forms plugins, mostly).
* `sandbox.map` — URL paths your web server serves via rewrites (e.g. a pattern library's assets), so the sandbox can mirror them.

Your own journey tests (form submissions, checkout flows) live in the theme's `tests/e2e/` — when present, they run alongside the derived passes.

## 🤖 CI

One caller workflow gives every push the full sandbox suite — no MySQL, no Docker, no database dump. WordPress core is downloaded bare and your theme's own `composer.json` provisions the parent and plugins:

{% code title=".github/workflows/shakedown.yml" %}
```yaml
name: Shakedown
on: [push, pull_request]
jobs:
  shakedown:
    uses: pressgang-wp/pressgang-shakedown/.github/workflows/shakedown.yml@main
    secrets:
      COMPOSER_AUTH: ${{ secrets.COMPOSER_AUTH }}   # ACF Pro credentials
```
{% endcode %}

The Trial Report and route matrix upload as artifacts on every run. Suits theme-shaped repos (the repo *is* the theme).

## 🛞 Better with the fleet

Shakedown works on any PressGang site out of the box, and gets sharper with its shipmates installed:

* **[Capstan](CAPSTAN.md)** — the matrix gains an *oracle*: each route annotated with the template and controller that *should* render it, asserted at runtime. Silent fallbacks to `index.php` become hard failures. `wp capstan doctor` also runs as a pre-flight, aborting before any browser launches if the theme's config is broken.
* **[Muster](MUSTER.md)** — powers the sandbox's ACF state fixtures. Without it, the sandbox still runs; it just skips seeding.

The sandbox also counts **PHP notices, warnings and deprecations on every request** — even when display and logging are off — and fails any route that raises one. A page can look perfect and still be noisy underneath.

## 🧯 Troubleshooting

* **"No WordPress found"** — run from inside the theme (or anywhere below `wp-config.php`), or set `sitePath` in the config.
* **Pattern-library themes** — if your Twig partials live outside the theme (e.g. `patterns/templates/`), register that path with Timber via a `timber/locations` snippet, and map its assets with `sandbox.map`.
* **Sandbox asset 404s** — that's `sandbox.map` territory: tell it what your server rewrites.
* **WooCommerce / WPML / multisite** — attached mode only for now; their schemas need the MySQL lane (on the roadmap).
* **State fixtures skipped** — Muster wasn't found; install it in the theme via Composer, or point `sandbox.musterPath` at a checkout.

{% hint style="info" %}
Shakedown is in active development (beta). Commands and config are stable in shape but may still grow — pin a tag once releases are cut, and expect the odd sharp edge to have a friendly error message.
{% endhint %}
