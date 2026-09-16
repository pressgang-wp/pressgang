---
description: >-
  End-to-end browser testing for PressGang themes with zero tests to write —
  the route matrix, fixtures, and checks are all derived from your theme.
---

# 🚢 Shakedown

A shakedown cruise is the sea trial of a new vessel: take her out, push every system, find what rattles before the passengers board. Shakedown does the same for your theme — and because PressGang themes declare their post types, taxonomies, templates and menus in `config/`, it can **derive the whole test suite from the site itself**. You write nothing to get started.

{% hint style="success" %}
**Start in the project you want to test.** Install Shakedown there, run `npx shakedown init`, then choose an attached, sandbox or regression run. Shakedown derives representative routes from your site; you do not maintain a separate generic URL list.
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
| `npx shakedown init` | Setup | Creates project configuration and ignores generated reports |
| `npx shakedown` | Attached | Runs every pass against your local site |
| `npx shakedown regression --against=production --candidate=staging` | Regression | Compares derived paths and captures paired desktop/mobile evidence |
| `npx shakedown matrix` | Attached | Prints the route matrix without running checks |
| `npx shakedown sandbox` | Sandbox | Spins up the throwaway WordPress, seeds fixtures, runs every pass |
| `npx shakedown sandbox --update-snapshots` | Sandbox | Re-mints visual regression baselines |
| `npx shakedown ui` | Attached | Playwright's UI / watch mode for the ordinary passes |
| `npx playwright show-report` | Attached / sandbox reports | Opens a Playwright HTML report, not a Regression Report |

## 📦 Installation and project setup

You need Node 20+, [WP-CLI](https://wp-cli.org/), and a local WordPress installation.
Choose the directory that will own your tests and reports. For a complete site
repository such as PenARC, use its project root. For a repository containing only
a theme, use the theme root. Keep using that same directory for subsequent commands.

```sh
cd ~/Projects/your-site
```

If it has no `package.json`, create a private one first:

```sh
npm init -y
npm pkg set private=true --json
```

Install a Shakedown release containing the commands you want to use:

```sh
npm install --save-dev @pressgang-wp/shakedown
npx playwright install chromium
```

Shakedown is a development dependency. `npx shakedown` invokes the installed CLI;
it does not mean you should run tests inside the Shakedown source repository.
Commit the package manifest and lockfile with your project.

If your npm release does not yet contain `init` or `regression`, use a development
checkout containing those commands. See [installing from a local checkout](SHAKEDOWN-REGRESSION.md#using-a-development-checkout).

## 🛠️ Initialise with `shakedown init`

From the project directory, run:

```sh
npx shakedown init
```

The command detects WordPress in the current directory, an ancestor, or common
locations such as `wp`, `wordpress`, `web/wp`, `public/wp` and `public`. It asks you
to confirm the WordPress directory and local URL, reading the URL through WP-CLI
when possible. It then asks for a production URL and optional staging URL.
Enter production to configure regression testing; leave it blank for attached-only
setup. The WordPress directory must contain `wp-load.php`.

`init` creates two project-level settings:

- **`shakedown.config.json`** — the WordPress location, local URL and any regression
  environments you supplied.
- **`.gitignore`** — appends `/.shakedown/` so generated reports are not committed.
  Existing ignore rules are preserved.

Commit these changes. `init` does not install packages, start tests, migrate your
database or create visual baselines. It will not overwrite a config or create a
second one below an existing parent config. If configuration already exists, edit
it directly and skip this step.

For setup without prompts:

```sh
npx shakedown init --site-path=./wp --base-url=https://your-site.test \
  --reference=https://example.org --staging=https://staging.example.org --yes
```

`--yes` accepts detected values without asking questions. Non-interactive runs
never wait for input; supply missing values with flags. Use
`npx shakedown init --help` to see all options. If WP-CLI cannot read the local URL,
provide `--base-url`; ambiguous WordPress locations require a choice or `--site-path`.

## ⚙️ How configuration works

Shakedown looks for `shakedown.config.json` in the current directory, then its
ancestors. A generated regression config looks like this:

```json
{
  "sitePath": "wp",
  "baseUrl": "https://your-site.test",
  "regression": {
    "references": { "production": "https://example.org" },
    "candidates": {
      "local": "https://your-site.test",
      "staging": "https://staging.example.org"
    }
  }
}
```

| Setting | Meaning |
| --- | --- |
| `sitePath` | Directory WP-CLI inspects. Relative paths resolve from the config file's directory. |
| `baseUrl` | Public URL of that local installation; also the source origin for derived routes. |
| `regression.references` | Named comparison references, usually production. |
| `regression.candidates` | Named updated sites, usually local and optionally staging. |

The config describes environments, not a hand-maintained test plan. Routes come
from the theme and WordPress state. Regression URLs are plain HTTP(S) origins,
without credentials or path prefixes. Selecting staging does not remove the need
for local WordPress discovery.

The **directory where you invoke Shakedown** is the workspace: reports, matrices,
`tests/e2e/` journeys and `tests/__screenshots__/` baselines are resolved there.
Finding a config in an ancestor does not move that workspace. Run consistently
from your chosen project root to keep artifacts together.

Attached testing can still work without a config when Shakedown finds
`wp-config.php` in the current directory or an ancestor and WP-CLI can read the
home URL. Regression needs named references and candidates. Additional settings
for fixtures, search and suppressions are covered [below](#additional-configuration).

## Regression tests

Once the local site is running, its migrations are applied and uploads are
available, compare it with production:

```sh
npx shakedown regression --against=production --candidate=local
```

Use `--candidate=staging` for a configured, anonymously reachable staging site.
Password-protected staging is not currently supported. Both sites receive
anonymous GET-only observations; no forms are submitted or baselines updated.

Shakedown derives one route plan and captures matching paths at desktop and mobile
sizes. It reports candidate health separately from reference differences: a
production defect does not make the candidate correct, and an intentional content
change is not automatically a regression.

At the end, open the printed `.shakedown/regression/run-…/index.html` path in your
browser. Expand **Route index**, read **Candidate correctness** and **Differences**,
then compare the paired screenshots. Click a screenshot to see it at full size.
This report is separate from `npx playwright show-report` and the ordinary Trial
Report. Each rerun creates a new folder; share the whole folder so screenshots
remain available.

See [Regression testing](SHAKEDOWN-REGRESSION.md) for the complete walkthrough,
result categories, exit codes and accepting intentional differences.

## ⚡ Attached tests

{% code title="Terminal" %}
```bash
npx shakedown
```
{% endcode %}

This uses your configured local site, or automatic discovery when no config is needed. The ordinary passes include visual baseline checks, so missing baselines can fail even when the page renders correctly. Want to inspect the derived routes first?

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

Attached and sandbox runs write `.shakedown/trial-report.html` — a self-contained, client-readable page: summary numbers, a screenshot preview per route, a route × pass matrix, and failures in plain English (no stack traces). Attach it to a PR, or send it with a handover. The developer-grade report with traces lives separately in `playwright-report/`, and `run.json` beside it carries the same run for anything that wants to consume it.

It reports what happened rather than the tidiest version of it. A route that failed and then passed on a retry is marked **flaky** with its first failure shown, not folded into the passes — on a shared server a retry absorbs a load transient, but the same signature can mean a race in your theme, and that's your call to make rather than the report's. Suppressed categories are listed. A run that checked nothing says so, instead of leaving the previous run's report sitting there looking current.

## Additional configuration

Merge optional settings into the config created by `init`; keep its site and
regression settings. For example, these settings control search and sandbox fixtures:

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

Your own journey tests (form submissions, checkout flows) live in the workspace's `tests/e2e/` — when present, they run alongside the ordinary attached/sandbox passes. Regression mode does not run those journeys.

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

* **"No WordPress found"** — run `npx shakedown init` from the project root and supply `--site-path` if discovery is ambiguous. For an existing config, check that `sitePath` resolves to the intended installation.
* **"Configuration already exists"** — setup is already present locally or in a parent directory. Edit that config; `init` deliberately does not overwrite or shadow it.
* **Unknown `init` or `regression` command** — your installed Shakedown version lacks the feature. Use a version or [development checkout](SHAKEDOWN-REGRESSION.md#using-a-development-checkout) containing it.
* **Cannot find the regression report** — open the `index.html` path printed by the regression command. `npx playwright show-report` opens the separate Playwright report.
* **Pattern-library themes** — if your Twig partials live outside the theme (e.g. `patterns/templates/`), register that path with Timber via a `timber/locations` snippet, and map its assets with `sandbox.map`.
* **Sandbox asset 404s** — that's `sandbox.map` territory: tell it what your server rewrites.
* **WooCommerce / WPML / multisite** — attached mode only for now; their schemas need the MySQL lane (on the roadmap).
* **State fixtures skipped** — Muster wasn't found; install it in the theme via Composer, or point `sandbox.musterPath` at a checkout.

{% hint style="info" %}
Shakedown is in active development (beta). Commands and config are stable in shape but may still grow — pin a tag once releases are cut, and expect the odd sharp edge to have a friendly error message.
{% endhint %}
