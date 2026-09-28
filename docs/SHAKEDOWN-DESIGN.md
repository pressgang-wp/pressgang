---
description: >-
  Why Shakedown is built the way it is — and how each layer works, with the
  third-party tools it stands on and where their documentation lives.
---

# 🧭 Shakedown: Design & Internals

The [Shakedown guide](SHAKEDOWN.md) explains installation, initialisation and configuration. The [Regression testing walkthrough](SHAKEDOWN-REGRESSION.md) covers production comparisons and reviewing findings. This page explains **why it's built this way** and **how each layer works** — useful when you're extending it, debugging it, or deciding whether to trust it.

## 🤔 Design decisions

### Why derived, not authored

Authored e2e suites die of maintenance: someone lists the URLs, writes the assertions, and forgets to update both. PressGang themes are different — the route surface is *declared* in `config/`, so the suite can be generated from the theme itself and never drifts. Add a CPT, gain its tests. Authored specs are reserved for the things derivation can't know: user journeys. This is the central bet; everything else follows from it.

### Why Playwright

Playwright is what WordPress core itself uses — all core and Gutenberg browser tests migrated from Puppeteer in 2023 ([announcement](https://make.wordpress.org/core/2023/10/16/wordpress-core-is-now-using-playwright-for-all-browser-based-tests/)). Practically it gives us, in one dependency: auto-waiting locators, an HTTP request client (pass 00 needs no browser), parallel workers, trace capture for post-mortem debugging, and first-party visual comparison — plus official axe-core bindings. The strongest alternative, [wp-browser/Codeception](https://wpbrowser.wptestkit.dev/), is excellent for PHP-side integration tests but its browser layer (WebDriver) is a generation behind, and a browser harness belongs in Node where the browser tooling lives. Docs: [playwright.dev](https://playwright.dev/docs/intro).

### Why an mu-plugin for the observer

The observer must run on **every request**, load **before plugins**, and require **no database state** — activation is a DB row, and Shakedown never writes to a real database. `mu-plugins/` is WordPress's canonical mechanism for exactly this: always loaded, can't be deactivated, and Composer-native (`"type": "wordpress-muplugin"` routes packages there via installer-paths). The alternatives are worse: drop-ins (`db.php`, `object-cache.php`) are single-occupancy and fought over by caching plugins; editing `wp-config.php` means mutating a file we don't own. Today the observer is installed **only into sandboxes**, assembled fresh each run.

### Why seed and fake — hence Muster

Three reasons real content can't be the fixture:

1. **Determinism.** Visual snapshots and stable selectors need repeatable content across runs and machines. [Muster](MUSTER.md) supplies the seeded [Faker](https://fakerphp.org/) sequence, while Shakedown separately pins one fixture epoch shared by post dates and ACF/Victuals date generation. Neither generated values nor relative dates consult the machine clock.
2. **Denominators.** Real content only exercises the states editors happen to have created. Fixtures derived from `acf-json` exercise the states that *can exist* — including the all-important **minimal state** (required fields only), where empty-link and missing-image bugs live. Real content found one such bug on BHP by luck; derivation finds them systematically.
3. **The hard rule.** Nothing ever writes to a real site's database. Seeding is therefore only possible in an environment that is disposable *by construction* — which is why Muster and the sandbox arrived together.

Muster borrows useful seeder ergonomics from several frameworks, then adapts
them to WordPress: fluent builders persist through core APIs, natural-key
lookups avoid blind inserts, and `--seed=N` controls generated fixture values
without introducing Models or an ORM.

### Why the sandbox is SQLite

The [SQLite Database Integration plugin](https://wordpress.org/plugins/sqlite-database-integration/) (the WordPress Performance team's own project, with a real MySQL-parser driver since 2025) lets a genuine PHP WordPress run with a single database *file* — no MySQL server, no Docker, nothing shared. The sandbox symlinks your **code** read-only and owns its **state** (config, uploads, database) in a temp dir: Laravel's in-memory test database, translated to WordPress. Isolation isn't assumed — every boot queries a witness endpoint and refuses to test unless `ABSPATH`, the content dir, and the database all resolve inside the temp directory.

{% hint style="warning" %}
**Hard-won:** PHP resolves `__DIR__` through symlinks, so entry PHP files are *copied* rather than symlinked — a symlinked `wp-load.php` would silently load the real site's config instead of the sandbox's.
{% endhint %}

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

Pass 00 compares the first two against the oracle (so a route silently falling back to `index.php` is a hard failure), and fails any route whose PHP-issue count is non-zero — notices are counted by an error handler even when display and logging are off.

Getting those headers out is fiddlier than it looks, and the timing is the whole trick. The observer buffers output for the entire request, then writes the headers from WordPress's `shutdown` action at **priority 0** — ahead of `wp_ob_end_flush_all` at priority 1, which is what flushes the buffer and commits the response. A plain `register_shutdown_function()` cannot work here: WordPress registers *its* shutdown handler at `wp-settings.php:166`, while mu-plugins don't load until `:498`, so WP's always runs first and `headers_sent()` is already true. Everything raised during rendering is therefore counted; anything raised later, by shutdown callbacks at priority 1 or beyond, is not — the honest cost of having to commit headers before the body goes out.

Because every one of those assertions is guarded on its header existing, an observer that stops answering would turn them all into no-ops that report success. So a sandbox run also asserts that the observer answered at all: silence is a failure, not a skip.

### The passes — Playwright

For attached and sandbox tests, Shakedown runs Playwright with a packaged config; the invocation directory is the *workspace* (reports, matrix, and baselines land there; a `tests/e2e/` dir joins the run as the journeys project). Pass 00 uses Playwright's [APIRequestContext](https://playwright.dev/docs/api-testing) (no browser — whole-site sweep in seconds); passes 01–03 drive Chromium. Failures retain a **trace** — open with `npx playwright show-trace <trace.zip>` for a time-travel replay ([trace viewer docs](https://playwright.dev/docs/trace-viewer)). The developer-grade HTML report lands in `playwright-report/` ([reporter docs](https://playwright.dev/docs/test-reporters)).

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

`shakedown sandbox` runs a bundled PHP script via WP-CLI inside the sandbox: [`AcfJson`](MUSTER.md) reads the theme's `acf-json/`, extracts each group's seedable location (post type / page template / options page), and `AcfValueGenerator` produces values for ~25 ACF field types — recursing through repeaters, groups, and flexible content (one row per layout, so every layout renders at least once). Media fields get generated placeholder images (colour derived from the slug — deterministic), relational fields get stub posts/terms. Two variants per group: `populated` and `minimal`. Options-page groups seed once so the site chrome (header/footer) renders fully. One explicit seed and fixture epoch are passed into Muster; the same clock pins both generated ACF dates and fixture post dates.

### The sandbox assembly — SQLite + wp server

The [SQLite Database Integration plugin](https://github.com/WordPress/sqlite-database-integration) is pinned to an exact version, fetched from wordpress.org, verified against a known SHA-256, and wired in via its `db.copy` drop-in template (`DB_DIR`/`DB_FILE` constants point at the temp dir). The cache is keyed by version and staged-then-renamed, so bumping the pin invalidates it by construction and an interrupted download can never become the cache — `latest-stable` would have meant two machines assembling sandboxes on two different database layers, underneath every visual baseline. The site is served by [`wp server`](https://developer.wordpress.org/cli/commands/server/) — WP-CLI's built-in PHP server with a WordPress-aware router — on an OS-assigned ephemeral port. `wp core install`, theme activation, and permalink setup all run against the throwaway database.

### CI — the reusable workflow

The [workflow](https://github.com/pressgang-wp/pressgang-shakedown/blob/main/.github/workflows/shakedown.yml) checks the theme out *into* a WordPress-shaped tree, then: [`shivammathur/setup-php`](https://github.com/shivammathur/setup-php) (PHP + WP-CLI + Composer), `wp core download --skip-content`, `composer install` in the theme (parent + plugins land via installer-paths; ACF Pro credentials via the `COMPOSER_AUTH` secret — [Composer auth docs](https://getcomposer.org/doc/articles/authentication-for-private-packages.md)), the workflow's pinned `muster-ref` fetched for fixtures, Capstan installed for the oracle, then `npx shakedown sandbox`. Composer and [Playwright browser caches](https://playwright.dev/docs/ci#caching-browsers) keep warm runs fast; the Trial Report uploads as an artifact either way.


### Project setup

`shakedown init` belongs to Shakedown rather than Capstan: it owns the runner's
configuration and artifact ignore rules. `lib/init.mjs` runs before target
resolution, discovers WordPress through a bounded set of paths, optionally reads
its home URL through WP-CLI, and collects named regression origins. Interactive
prompts and explicit flags feed the same validation. Non-interactive invocations
never wait for input. Existing local or ancestor configs are preserved; relative
site paths resolve against the config directory. Setup writes only project-local
config and ignore files, never WordPress data, baselines or dependencies.

Configuration discovery and the workspace are distinct. The nearest ancestor
config supplies target settings; relative `sitePath` values are anchored there.
Reports and consumer journeys remain anchored to the invocation directory, so
users should run from the consumer project root even when the executable is
installed through a local link to the Shakedown checkout.

### Regression — one discovery matrix, two observed environments

`lib/target.mjs` retains local discovery and resolves named reference/candidate
origins from the same target's `regression` object. `lib/regression-plan.mjs`
creates a separate versioned paired plan preserving kind, expected status, oracle
metadata, exact path/query and provenance. It rejects foreign-origin and unsafe
routes, with exclusions disclosed. Normal attached/sandbox matrices are not
rewritten for remote origins.

`lib/regression.mjs` runs Capstan/fallback derivation plus supplementary families
inside a unique evidence directory. Production homepage navigation adds at most a
configured number of same-origin links; it is explicitly a sample, not a full
inventory. Sitemaps are deferred to avoid quietly growing a second crawler. Missing
paths are never inferred solely from a sampled matrix. Shared paths compare
directly; different redirect destinations remain unmatched.

`lib/health.mjs` holds correctness checks shared with passes 00–02. Production
parity is never a correctness assertion. Candidate health blocks, while semantic
and visual differences begin advisory until narrower rules have field evidence.
The existing sandbox baseline pass and consumer journeys are never collected by
regression. Each route, viewport and side gets a fresh context, without retries;
an unsuccessful capture cannot be overwritten by a successful retry.

Before collecting evidence, bounded scrolling triggers lazy images, then returns
to the top. The report records scroll limits and pending images; broken lazy
images join health checks when the page bottom was reached. The runner owns
SIGINT/SIGTERM handling so interruption preserves an explicit incomplete report
before browser teardown.

`lib/regression-browser.mjs` enforces anonymous GET-only requests below JavaScript.
Redirects are inspected before following; top-level and iframe navigation cannot
leave the selected origin. Non-GET requests, WebSockets, service workers, known
administrative/action URLs and cookies/authorization are blocked. A separate HTTP
context is necessary: Playwright's `route.fetch()` populates the browser cookie jar
before a caller strips response cookies. An adversarial browser fixture proves
that requests cannot bypass these restrictions. Blocked traffic is evidence because
these restrictions can change the rendered page. As in attached mode, this does
not control WordPress/plugin side effects of serving a GET or booting WP-CLI.

Semantic evidence uses normalized title/H1, landmarks and positions, visible form
schemas, image URLs and natural/displayed dimensions, empty links/headings and a
basic content structure sequence. No CSS class is assumed to mean a card. Hidden
form state and exact body text are excluded; dynamic selectors and accepted
finding signatures require explicit disclosed policies. Different image identities
or sampled posts are not relabelled as equivalent content.

`lib/regression-report.mjs` writes a distinct Regression Report alongside JSON and
runtime PNGs. Candidate health, differences, additions/removals, accepted differences
and inconclusive/unmatched evidence stay separate. Reports are initialized before
discovery, updated during work and retained per run; a failed discovery cannot
leave an old all-clear looking current. Interstitial titles trigger an explicit
incomplete result and stop additional route traffic. These heuristics are evidence
of uncertainty, not proof that an access barrier exists.

The summary counts route/viewport observations, not unique paths or root causes.
A complete execution can still exit 1 for health failures or 2 for unmatched or
inconclusive evidence. Advisory differences alone can exit 0 and still require
review. Regression reports are opened directly as HTML; the Playwright report
viewer for the whole suite and ordinary Trial Report belong to the other execution
path. Regression findings additionally link to a generated Playwright visual
comparison viewer for each compared screenshot pair.

Paired regression screenshots use the selected viewport width and full document
height without changing page CSS. Playwright `toMatchSnapshot` compares disposable
reference/candidate captures (threshold 0.2, zero allowed differing pixels); these
captures never become approved baselines. Horizontal scroll range and clipped
element evidence are assessed separately from the visible screenshot comparison.

The regression `accessibility` setting accepts `"on"` (default) or `"off"`, with a
CLI override. Disabling it leaves visual/structural comparison intact and discloses
the omitted audit. Axe findings describe candidate accessibility health rather
than a reference-to-candidate accessibility comparison.

ACF's regression role is deliberately narrower than fixture generation. Locations
can identify representative content surfaces, but optional relationships,
conditional groups and nested flexible content do not establish a universal
field-to-DOM contract. A future read-only coverage inventory and explicit PressGang
markup conventions can close that gap without duplicating Capstan or Muster.
The initial release does not seed existing content or claim complete ACF coverage.


### Compact regression report projections

`lib/report-compact.mjs` builds reviewer-facing projections without mutating the
saved run. Technical arrays cancel equal values as multisets; unmatched values
are not guessed pairs. Image-content panels exclude position-only movement,
while screenshot/layout comparisons and raw coordinates preserve it. Legacy
captures without usable array mappings retain their full evidence group.

Identical candidate image-aspect-ratio advisories share a representative by
message, target, dimensions and suppression state. Route counts remain visible;
raw findings, verdicts and exit status stay unchanged. The complete omitted-route
inventory stays in JSON. Element evidence uses one close-up plus an original-image
link, acceptance guidance is global, and empty transport panels are omitted.

### Planned work versus current features

Regression currently runs Chromium. Optional Firefox/WebKit coverage, richer
regression traces and ARIA snapshot evaluation are roadmap items, not supported
configuration options. See `docs/ROADMAP.md` in the Shakedown repository.
The Shakedown repository's `docs/CI-DEPLOYMENT-SPEC.md` is a proposal for PR reports, gate policy, approvals and notifications. It does not
add a shipped deployment gate, Slack integration or retained-reference importer.
The existing reusable workflow remains the sandbox testing lane.
