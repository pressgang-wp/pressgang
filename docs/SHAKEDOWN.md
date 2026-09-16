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

Shakedown runs in one of three **modes** — keep the distinction in mind, everything below builds on it:

| Mode | Answers | Touches your database? |
| --- | --- | --- |
| **Attached** — your live local site | "Is my site healthy *right now*?" | Never writes — read-only GETs |
| **Regression** — derived production/candidate comparison | "What changed, and is the candidate healthy?" | Anonymous GETs; browser writes blocked |
| **Sandbox** — a disposable throwaway WordPress | "Is my *theme* correct, independent of content?" | N/A — its own database, vaporised after |

| Command | Mode | What it does |
| --- | --- | --- |
| `npx shakedown` | Attached | Runs every pass against your local site |
| `npx shakedown regression --against=production --candidate=staging` | Regression | Compares derived paths and captures paired desktop/mobile evidence |
| `npx shakedown matrix` | Attached | Prints the route matrix without running checks |
| `npx shakedown sandbox` | Sandbox | Spins up the throwaway WordPress, seeds fixtures, runs every pass |
| `npx shakedown sandbox --update-snapshots` | Sandbox | Re-mints visual regression baselines |
| `npx shakedown ui` | Either | Playwright's UI / watch mode, for fixing failures |
| `npx playwright show-report` | Either | Opens the last HTML report |

## 📦 Install

You need Node 20+, [WP-CLI](https://wp-cli.org/), and your site running locally (any server — Herd, Valet, DDEV, MAMP… it's just a URL). From inside your theme:

{% code title="Terminal" %}
```bash
npm i -D @pressgang-wp/shakedown
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

It also covers the surfaces that are easy to forget because nothing links to them prominently:

| Family | Why it's there |
| --- | --- |
| **Author** archives | `author.php` is a template most themes ship and few ever open |
| **Date** archives | likewise `date.php` — year and month, taken from your newest post |
| **Pagination** | page 2 of any archive with more posts than fit; where off-by-one and empty-page bugs live |
| **Feeds** | the main feed and per-post-type feeds — a feed that fatals is still a broken site |
| **Empty search** | a term that matches *nothing*, so the no-results branch gets exercised — your `searchTerm` is chosen to find things |

Feeds are checked by pass 00 only: a full-page screenshot or an axe audit of XML measures nothing. Page 2 appears only when a post type genuinely has more published posts than `posts_per_page`.

## 🧪 What gets checked

| Pass | Checks |
| --- | --- |
| **00 · Availability** | Right HTTP status · no PHP/Twig error output · a `<title>` present. HTTP-only, so it sweeps the whole site in seconds. |
| **01 · Integrity** | Real Chromium render: no JS exceptions, console errors, failed requests, or broken images. |
| **02 · Accessibility** | axe-core against WCAG 2.1 A/AA. Serious/critical violations fail; minor ones report as advisory. |
| **03 · Visual** | Full-page screenshots against baselines committed in your theme. Missing baselines fail; only an explicit sandbox update writes them. |

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

This assembles a **throwaway WordPress** in a temp directory: your code symlinked read-only, its own fresh SQLite database, its own uploads — think Laravel's in-memory test database, for WordPress. Its install defaults (Hello world!, Sample Page, the seeded comment) are cleared first, so only seeded fixtures exist and no version-dependent default content leaks into feeds, archives, or menus. It then seeds in three layers, convention-first:

1. **Your theme's own fixtures.** If the theme ships [Muster](MUSTER.md) seeders (a top-level `muster/` directory), the sandbox runs them via `wp capstan seed` — so your real menus, terms, pages and relationships are present, exactly as on a dev site. A theme that ships none is unaffected; the derived layer below still covers it.
2. **Derived ACF state fixtures.** On top, for every field group, one page/post with *every field populated* and one with *only required fields* — the sparsest content an editor can legally publish, which is exactly where empty-link and missing-image bugs live.
3. **Per-journey scenarios.** Finally, any `tests/e2e/*.setup.php` in your theme runs, so an authored journey can arrange the precise, deterministic scenario its paired `*.spec.mjs` asserts on.

Then it runs all passes and vaporises.

```
⚓ sandbox up at http://127.0.0.1:54223 (isolation verified)
⚓ seeded theme baseline via `wp capstan seed`
⚓ seeded 34 ACF state fixtures via Muster
⚓ capstan doctor: 11 checks, 0 failures, 0 warnings
⚓ 22 routes (via capstan)
127 passed (18s)
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

{% hint style="info" %}
**Baselines are only ever written on purpose.** A route with no baseline *fails* rather than quietly acquiring one, and `--update-snapshots` is refused outside sandbox mode. A baseline captured from your live site records whatever was published that day — and because singles are sampled newest-first, the very pages it captured drift as you edit. Deterministic fixtures are what make a snapshot mean something, so baselines come from the sandbox or not at all.
{% endhint %}

Everything a baseline rests on is pinned to an exact version: the WordPress core it was rendered against, the Muster revision that generated the fixtures, and the SQLite drop-in underneath (verified by checksum on download). Otherwise an upstream release could rebreak every baseline you own on its release day, with no change on your side.

## 📋 The Trial Report

Every run writes `.shakedown/trial-report.html` — a self-contained, client-readable page: summary numbers, a screenshot preview per route, a route × pass matrix, and failures in plain English (no stack traces). Attach it to a PR, or send it with a handover. The developer-grade report with traces lives separately in `playwright-report/`, and `run.json` beside it carries the same run for anything that wants to consume it.

It reports what happened rather than the tidiest version of it. A route that failed and then passed on a retry is marked **flaky** with its first failure shown, not folded into the passes — on a shared server a retry absorbs a load transient, but the same signature can mean a race in your theme, and that's your call to make rather than the report's. Suppressed categories are listed. A run that checked nothing says so, instead of leaving the previous run's report sitting there looking current.

## ⚙️ Configuration

None required. An optional `shakedown.config.json` in the theme handles the exceptions:

{% code title="shakedown.config.json" %}
```json
{
  "searchTerm": "research",
  "sandbox": {
    "plugins": ["contact-form-7"],
    "map": { "assets": "patterns/public/assets" },
    "seed": 42,
    "epoch": "2026-01-01T09:00:00+00:00"
  }
}
```
{% endcode %}

* `searchTerm` — a word that actually appears in your content, for the search probe.
* `sandbox.plugins` — plugins the sandbox should activate (forms plugins, mostly).
* `sandbox.map` — URL paths your web server serves via rewrites (e.g. a pattern library's assets), so the sandbox can mirror them.
* `sandbox.seed` — integer controlling Muster's deterministic generated-value sequence.
* `sandbox.epoch` — timezone-qualified ISO 8601 fixture datetime used by post
  dates and every relative Victuals/ACF date helper. Randomness and time are
  separate inputs.

Your own journey tests (form submissions, checkout flows) live in the theme's `tests/e2e/` — when present, they run alongside the derived passes.

### 🔇 Suppressing what isn't yours

The passes are strict on purpose: zero console errors, zero PHP notices, no serious axe violations. On a real site some of that noise belongs to somebody else — a tag manager logging to the console, a deprecation raised inside ACF on a newer PHP, a contrast rule your palette loses deliberately. An `ignore` block records what's already been judged, so you never have to choose between the noise and switching a whole pass off:

{% code title="shakedown.config.json" %}
```json
{
  "ignore": {
    "routes":        ["/private-area", "/wp-json/"],
    "consoleErrors": ["googletagmanager", "ERR_BLOCKED_BY_CLIENT"],
    "requests":      ["/wp-json/"],
    "phpIssues":     ["wp-content/plugins/advanced-custom-fields-pro/"],
    "errorSignatures": ["on https://mysite.test/alerts/"],
    "a11yRules":     ["color-contrast"]
  }
}
```
{% endcode %}

Patterns are plain **substrings**, case-sensitive — not regex, not globs. Paste the text out of a failure message and it's the pattern that silences it. `a11yRules` is the exception: exact axe rule IDs, handed to axe's own `disableRules()`.

`phpIssues` matches the whole signature — `<message> in <path>:<line>`, the path relative to WordPress — so a pattern can name an **origin** (`wp-content/plugins/…/`) as easily as a message, and stays portable across machines and CI.

`errorSignatures` covers the awkward case where a page's *content* legitimately reads "Warning: " — a health site's article about risk, say. It matches `<signature> on <url>`, so naming the URL suppresses the body scan for that one page rather than disabling it everywhere.

{% hint style="warning" %}
**Nothing is suppressed quietly.** Every active pattern is printed when the matrix is derived, ignored routes are counted, and the Trial Report ends with a *Suppressed by configuration* table — so a clean run can be read for what it is. A mistyped key (`consoleError`, singular) is an error rather than a silent no-op that suppresses nothing while looking like it works.
{% endhint %}

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

The Trial Report and route matrix upload as artifacts on every run. Suits
theme-shaped repos (the repo *is* the theme).

Versions are **pinned, not floating**: `wp-version` and `muster-ref` both default
to exact revisions verified against that Shakedown release, so neither core
markup nor fixture behaviour can drift when an upstream `main` moves. Pass
`latest` explicitly when you want to test forward compatibility on purpose.

## 🛞 Better with the fleet

Shakedown works on any PressGang site out of the box, and gets sharper with its shipmates installed:

* **[Capstan](CAPSTAN.md)** — the matrix gains an *oracle*: each route annotated with the template and controller that *should* render it, asserted at runtime. Silent fallbacks to `index.php` become hard failures. `wp capstan doctor` also runs as a pre-flight, aborting before any browser launches if the theme's config is broken.
* **[Muster](MUSTER.md)** — runs the theme's own seeders as the sandbox baseline (via `wp capstan seed`) and powers the derived ACF state fixtures on top. Without it, the sandbox still runs; it just skips seeding.

Route, controller, context, and config-dump validation belong to this
Capstan/Shakedown boundary. Do not duplicate them in PHPStan conventions or in
new Capstan commands: PHPStan handles static PHP signals, while Shakedown uses
Capstan's runtime oracle to prove what the theme actually renders.

The sandbox also counts **PHP notices, warnings and deprecations on every request** — even when display and logging are off — and fails any route that raises one. A page can look perfect and still be noisy underneath. The failure quotes the signature verbatim, so if the notice comes from a dependency rather than your theme you can paste it straight into `ignore.phpIssues`.

## 🧯 Troubleshooting

* **"No WordPress found"** — run from inside the theme (or anywhere below `wp-config.php`), or set `sitePath` in the config.
* **Pattern-library themes** — if your Twig partials live outside the theme (e.g. `patterns/templates/`), register that path with Timber via a `timber/locations` snippet, and map its assets with `sandbox.map`.
* **Sandbox asset 404s** — that's `sandbox.map` territory: tell it what your server rewrites.
* **WooCommerce / WPML / multisite** — attached mode only for now; their schemas need the MySQL lane (on the roadmap).
* **State fixtures skipped** — Muster wasn't found; install it in the theme via Composer, or point `sandbox.musterPath` at a checkout.

{% hint style="info" %}
Shakedown is in active development (beta). Commands and config are stable in shape but may still grow — pin a tag once releases are cut, and expect the odd sharp edge to have a friendly error message.
{% endhint %}


## Comparing production with an updated child theme

Run `npx shakedown init` from the consumer project to create its configuration.
The setup detects local WordPress and reads its home URL, then asks for production
and optional staging origins. It creates `shakedown.config.json` and adds
`/.shakedown/` to `.gitignore`; commit both files. It does not install dependencies,
run tests or modify WordPress data. Existing configs are never overwritten or
shadowed. For scripted setup:

```sh
npx shakedown init --site-path=./wp --base-url=https://theme.test \
  --reference=https://example.org --staging=https://staging.example.org --yes
```

Paths resolve relative to the config file. Use `shakedown init --help` for options.
Leave production blank in interactive setup for attached-only testing. Run reports
belong to the invocation directory, so run tests from the same consumer project.

Regression separates **discovery** (a local WordPress/PressGang installation),
**reference** (production) and **candidate** (local or staging). Existing `sitePath`
and `baseUrl` identify discovery. Add named environments to the same target config:

```json
{
  "sitePath": "/path/to/wordpress",
  "baseUrl": "https://theme.test",
  "regression": {
    "references": { "production": "https://example.org" },
    "candidates": {
      "local": "https://theme.test",
      "staging": "https://staging.example.org"
    }
  }
}
```

```sh
npx shakedown regression --against=production
npx shakedown regression --against=production --candidate=staging
```

The existing Capstan/fallback pipeline and supplementary families derive the plan.
Exact path and query identify content; two sampled posts are never paired merely
because their post type matches. A bounded production homepage-navigation
supplement exposes possible removals or discovery gaps without becoming a crawler.

Each run writes `.shakedown/regression/run-<unique>/index.html`, JSON and paired
screenshots at desktop/mobile widths. Share the whole folder. These captures are
run artifacts, never committed sandbox baselines. The ordinary Trial Report and
matrix remain untouched. Candidate health failures, differences, additions,
reference-only routes, accepted differences and inconclusive comparisons are
reported separately. Production itself can be defective.

Captures scroll through each page within a fixed budget before returning to the
top, so lazy images can load. Pending images or a reached scroll limit are
disclosed in the report rather than silently treated as complete evidence.

Candidate health shares passes 00–02: expected status, PHP/Twig signatures, title,
optional observable oracle, JavaScript/console/request errors, broken images and
serious/critical WCAG violations. Comparison evidence covers redirects, title/H1,
landmarks, image dimensions, empty links/headings, form structure and major
main-content elements. Screenshots and semantic differences are initially advisory;
exact text/pixel equality is not a gate. Exit 1 means candidate health failures;
exit 2 means incomplete or inconclusive/unmatched evidence and takes precedence.
A maintenance/access interstitial stops early with remaining coverage disclosed.

Both sides receive anonymous GET-only traffic. Browser writes, WebSockets,
service workers and unsafe navigation are blocked; cookies and credentials are not
sent. Third-party frame navigations are blocked and disclosed. No login, form
submission, authored journey, database mutation command, observer installation or
baseline update runs. There are no automatic retries to hide a transient failure.

Optional `regression` settings:

- `viewports`: named `{name, width, height}` entries; defaults 1280×900 and 390×844.
- `navigationLimit`: 0–200, default 40; zero disables reference navigation discovery.
- `timeout`: 1000–120000ms per operation, default 20000.
- `criticalRoutes`: additional paths, never a replacement for derived coverage.
- `ignoreSelectors`: dynamic regions omitted from semantics and masked in screenshots;
  candidate health still checks them.
- `accept`: case-sensitive substring signatures such as `title on /about/`.
  Accepted differences remain in evidence rather than disappearing.
- `defaultReference` / `defaultCandidate`: default named selections, initially
  `production` / `local`.

All policies, exclusions and blocked traffic are disclosed. Unknown config keys
fail. Environment URLs must be plain HTTP(S) origins without credentials or path
prefixes. Normalization collapses whitespace, maps same-origin URLs to paths and
omits hidden form values; it does not silently remove random staff or editorial
content changes. ACF locations motivate representative post-type/template coverage,
but schema alone does not prove rendered component content; complete ACF coverage
and field-to-DOM assertions are not claimed.

See [the regression contract](https://github.com/pressgang-wp/pressgang-shakedown/blob/main/docs/REGRESSION.md)
for precise classification and safety limitations.
