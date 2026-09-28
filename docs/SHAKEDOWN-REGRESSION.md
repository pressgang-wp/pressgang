---
description: >-
  Install Shakedown in your project, compare an updated site with production,
  and review evidence at selected viewport sizes.
---

# Regression testing

Use regression mode when you have updated a theme and want to compare the result
with the deployed site. Shakedown discovers representative routes from local
WordPress and the theme's declarations, then visits the same paths on both sites.
You do not maintain a separate generic URL list or write tests for each page.

The workflow is **install → initialise → compare → review → fix and rerun**.
Run these commands in the project you are testing, such as PenARC. That project
owns the configuration and reports, even when Shakedown is installed from a
separate development checkout.

## 1. Install in your project

You need Node 20+, WP-CLI, and a local WordPress installation that WP-CLI can
inspect. Start in the directory where you want configuration and reports to live:

```sh
cd ~/Projects/your-site
```

If that directory has no `package.json`, create a private project manifest first:

```sh
npm init -y
npm pkg set private=true --json
```

For an npm release containing `init` and `regression`, install the package and its
browser:

```sh
npm install --save-dev @pressgang-wp/shakedown
npx playwright install chromium
```

### Using a development checkout

If your installed release does not contain these commands, use a Shakedown
checkout that does. With the two repositories beside each other, prepare its
dependencies and browser once:

```sh
cd ../pressgang-shakedown
npm ci
npx playwright install chromium
cd ../your-site
npm install --save-dev ../pressgang-shakedown
```

The dependency will be `file:../pressgang-shakedown`. It uses that checkout's code
and requires that relative directory to remain available. For teammates and CI,
use a released version containing the feature or provide the same checkout layout.
The browser installation above runs in Shakedown's checkout because npm may not
expose dependency binaries through a local package link.

Verify the CLI from your site project:

```sh
npx shakedown init --help
```

If this reports an unknown command, check which Shakedown version or checkout you
installed before continuing.

## 2. Initialise the project

From the same project directory, run:

```sh
npx shakedown init
```

The prompts ask for:

| Prompt | What to enter |
| --- | --- |
| WordPress directory | The directory containing `wp-load.php`, often `wp`. Press Enter to accept a detected path. |
| Local public URL | Your site's browser URL, for example `https://your-site.test`. The default is read through WP-CLI. |
| Production URL | The reference site, for example `https://example.org`. Enter this to enable regression setup. |
| Staging URL | An optional second candidate. Leave it blank if you only need local testing. |

If WordPress cannot be detected, enter its path. If multiple installations are
found, choose the intended one. If WP-CLI cannot read the home URL, enter it
explicitly. Environment URLs must be HTTP(S) origins without credentials, path
prefixes, queries or fragments.

Setup creates `shakedown.config.json` and adds `/.shakedown/` to `.gitignore`.
Commit those files and the package manifest/lockfile with your project. Generated
reports remain untracked. Setup does not install dependencies, start tests, run
migrations or change WordPress data.

A typical generated config is:

```json
{
  "sitePath": "wp",
  "baseUrl": "https://your-site.test",
  "regression": {
    "defaultViewports": ["desktop", "tablet", "mobile"],
    "references": {
      "production": "https://example.org"
    },
    "candidates": {
      "local": "https://your-site.test",
      "staging": "https://staging.example.org"
    }
  }
}
```

`sitePath` is relative to the config file. It identifies the local installation
used for **discovery**. `baseUrl` is that installation's public origin.
`references` and `candidates` name the environments to compare. The production
and selected candidate origins must differ.

{% hint style="info" %}
Already have a config? Skip `init` and inspect or edit it. The command refuses to
overwrite an existing config or shadow one in a parent directory. Leaving the
production prompt blank creates attached-only configuration; add the `regression`
section later if you want comparisons.
{% endhint %}

For scripted setup, supply the values as flags:

```sh
npx shakedown init --site-path=./wp --base-url=https://your-site.test \
  --reference=https://example.org --staging=https://staging.example.org --yes
```

`--yes` skips interactive questions and uses detected values where possible.
Non-interactive runs never wait for input; missing required values produce an
error. This is a single-project setup command. Existing central configurations
with a `targets` map are edited directly and selected with `--target=<name>`.

## 3. Prepare the site and start the comparison

Make sure local WordPress and its database are running. Apply your project's
migrations and make its uploads available before testing. Similar content on both
sides makes differences easier to interpret, but Shakedown does not import a
database or require production content to be identical.

Compare production with local:

```sh
npx shakedown regression --against=production --candidate=local
```

Or select your configured staging candidate:

```sh
npx shakedown regression --against=production --candidate=staging
```

Local WordPress is still needed for discovery when the candidate is staging.
Keep running from the project directory: it determines where reports are written.

The run derives its plan through Capstan (or the bundled fallback), adds
supplementary routes such as feeds and page 2, and can add a bounded sample of
production navigation links. It checks candidate health and captures paired
screenshots at the selected viewport sizes in full runs. Feeds receive HTTP checks only. This is
sampled coverage by default. Use `--coverage=exhaustive` to add the discovered
public content inventory, or `--routes` to investigate particular discovered paths.

Both sites are observed anonymously using GET requests. No forms are submitted,
no observer is installed on production, and no sandbox baselines are created or
updated. Password-protected or login-only staging is not supported by this mode;
use a reachable local candidate. Blocked traffic and access failures are disclosed.

Let the command finish. Larger sites can take several minutes. A failure exit code
does not mean the report is missing: read the final output for its location.

## 4. Open the findings

The terminal prints a path like:

```text
Regression report: /path/to/your-site/.shakedown/regression/run-AbCd12/index.html
```

Open **that run's `index.html`** in your browser. On macOS, using the actual path
printed by your run:

```sh
open .shakedown/regression/run-AbCd12/index.html
```

You can also open the file through Finder or your editor. `.shakedown` is a hidden
directory; on macOS, Command–Shift–Period toggles hidden files in Finder.

{% hint style="info" %}
The Regression Report is separate from the ordinary Trial Report and Playwright
HTML report. `npx playwright show-report` does not open it. There is no dedicated
Shakedown report-opening command yet; open the printed HTML file directly.
{% endhint %}

Work through the report in this order:

1. **Check the run state and summary.** “Complete” means execution finished, not
   that the candidate passed. Counts are route/viewport observations: a page
   checked on desktop and mobile contributes two observations. The health count
   is observations with findings, not the number of unique underlying bugs.
2. **Check coverage and Repeated changes.** Confirm that the route and viewport
   you care about were visited. Review repeated changes together, then use Route
   index to open individual page evidence.
3. **Check the route status and Candidate correctness when present.** Detailed
   HTTP/rendering, JavaScript, asset and enabled accessibility failures appear
   here. Routes without recorded health failures use a compact heading status.
4. **Read Behaviour changes and Presentation and content changes.** Start with
   the plain-language summaries. Expand Technical evidence for the concise delta; full arrays remain in `run.json`.
5. **Compare the screenshots.** Reference is production; candidate is the updated
   site. Click a thumbnail to view the full-size capture, or open the highlighted
   Playwright comparison under Appearance changed when available.
6. **Check transport restrictions and capture limits.** Blocked third-party
   requests, pending lazy images or a reached scrolling limit can affect evidence.

The report separates these outcomes:

| Finding | How to interpret it |
| --- | --- |
| Candidate health failure | A correctness check failed. Production may have the same problem. |
| Difference from reference | Something changed; decide whether it is intended, content drift or a defect. |
| Candidate-only | The candidate succeeds while the reference returns 404/410; a possible addition. |
| Reference-only | The reference succeeds while the candidate returns 404/410; a possible removal or discovery/content gap. |
| Unmatched | The endpoints reach different paths, or neither contains the sampled content. Do not compare them as the same page. |
| Inconclusive | Access, transport or server problems prevented a reliable comparison. |
| Accepted difference | An explicit config rule matched it; the evidence remains visible. |

A difference count is **not a defect count**. A smaller image derivative can be
intentional if its displayed size and quality remain suitable. A missing dropdown
option may be intentional when the candidate hides empty terms. Confirm the reason
before accepting it. Production is a reference, not proof of correctness.

Missing or broken image sources can fail candidate health checks. Possible image
distortion and document horizontal overflow are advisories: review their element
selectors and highlighted screenshots. Intentional `object-fit: cover` or `contain`
cropping is excluded from the distortion check; clipped carousel tracks alone do
not establish document overflow. These checks also run at the errors level.

Structural comparison needs exactly one captured `<main>` on each side. Missing
or ambiguous regions produce a disclosed limitation, not a claim that every local
structure was newly added. Location evidence for empty links is retained, but
movement alone does not establish an empty-link change.

## 5. Fix, rerun and share

After fixing code or correcting local content, run the same command again. Each
invocation creates a new `run-…` directory, so previous evidence remains available.
There are no automatic retries within a run. Old run folders are not automatically
removed; keep the evidence you need and delete older generated folders yourself.

Zip the **whole run directory** to share it. It contains `index.html`, `summary.md`, paired and diff PNGs where captured,
`run.json`, `plan.json` and the discovery matrix. Sending the HTML file alone loses
the screenshots. Keep sandbox baselines in `tests/__screenshots__/` separate from
these generated reference/candidate captures.

Use **Download Markdown summary** or open `summary.md` for a text summary suitable
for a pull request or handover. It includes health failures, behaviour changes,
accepted differences, limitations, screenshot guidance and coverage counts. Keep
the HTML and raw evidence available for the complete review.

For scripts and CI, exit codes mean:

| Code | Meaning |
| --- | --- |
| `0` | Run completed without candidate health failures or unmatched/inconclusive comparisons. Advisory differences can still need review. |
| `1` | Candidate health checks failed, or setup/configuration failed before the run. |
| `2` | Run was incomplete, empty, unmatched or inconclusive; this takes precedence over health failures. |

## Accepting intentional differences

Review a finding before adding a policy. Existing top-level `ignore` settings
apply to health checks and route exclusions. Inside `regression`:

- `accept` takes case-sensitive substring signatures, for example
  `"title on /about/"`. Accepted differences remain in the report.
- `ignoreSelectors` takes CSS selectors for dynamic content, such as a random
  staff selection. Matching elements are excluded from semantic comparison and
  masked in screenshots on both sites; candidate health still checks them.

All policies are disclosed. No date, random-content or text suppression is added
automatically. Invalid regression keys and selectors fail rather than silently
removing evidence.

Expand **Accept this difference in future runs** beside a comparison finding for
a JSON fragment. Merge its entry into your existing `regression.accept` array;
do not replace the rest of your configuration. For example:

```json
"accept": ["forms on /making-a-difference/"]
```

This accepts all form differences matching that signature, across viewports and
future runs, including future changes beyond the one reviewed. Substring matching
can also match longer route names. It does not clear HTTP or other health failures.
Remove the entry when you want that comparison reviewed again.

For an intentionally removed route, a separate top-level `ignore.routes` entry
excludes its checks altogether. Use that only when excluding the route is intended;
accepting a status difference alone will not clear its candidate health failure.

## Other settings and limits

| Regression setting | Default / purpose |
| --- | --- |
| `viewports` | Custom sizes: entries contain `name`, `width`, `height`; preset names can be overridden. |
| `defaultViewports` | New setup selects desktop, tablet and mobile. Existing explicit selections are preserved. |
| `coverage` | `sampled`; choose `exhaustive` to add eligible published public content and public terms, including empty terms. CLI `--coverage` overrides it. |
| `accessibility` | `"on"`; set `"off"` to skip axe while retaining the chosen comparison level. CLI `--accessibility` overrides it. |
| `defaultLevel` | `full` unless set to `errors` or `core`; CLI `--level` overrides it. |
| `navigationLimit` | 40 production-navigation links, bounded to 0–200; zero disables that supplement. |
| `timeout` | 20,000 ms per operation, configurable from 1,000–120,000 ms. |
| `criticalRoutes` | Extra paths to supplement the derived matrix, never replace it. |
| `defaultReference` / `defaultCandidate` | `production` / `local`. |

Forms are inspected but not submitted. Generic JavaScript interaction testing
(including carousel controls, menus and filter operation) remains deferred.
Screenshot diffs are advisory rather than pixel-equality gates. ACF schema helps identify
representative surfaces but does not establish that every relationship or rendered
component should be populated; complete ACF coverage is not claimed. The runner
does not control WordPress/plugin side effects of serving GET requests or booting
WP-CLI.

For implementation details, see [Design & Internals](SHAKEDOWN-DESIGN.md).

## Accessibility and deployment review

Accessibility checks come from Deque's axe-core through `@axe-core/playwright`;
Playwright supplies the browser. They check WCAG 2.0/2.1 A and AA rules on the
candidate, not differences from production. A serious or critical rating describes
accessibility impact and currently contributes a candidate health failure; it
does not establish that this release introduced the issue or assign release risk.

To review visual and structural regressions without an accessibility audit:

```sh
npx shakedown regression --level=full --accessibility=off
```

Save `"accessibility": "off"` inside `regression` for a project preference, or use
`--accessibility=on` to override it for one run. The default is on for core/full;
errors always omits the audit. The report explicitly lists accessibility as not
checked when disabled. This switch affects regression mode only, not attached
or sandbox passes. Alternatively, `ignore.a11yRules` narrows individual rules.

For deployment review, distinguish candidate application failures, unexpected
reference differences, intentional changes and existing accessibility debt.
Review incomplete coverage and visual changes even when the exit code is zero.
A passing selected suite is evidence for a release decision, not a guarantee
that every route, browser or interaction works.

## Identifying accessibility elements

Under **Accessibility: element details**, expand a rule and then an element.
Each element includes its selector, escaped HTML, axe's explanation and check
data. Contrast checks include measured and required ratios and foreground and
background colours when axe provides them.

The close-up outlines the element on a saved screenshot. Use **Open original
screenshot** for full-page context. A second full-page overlay is no longer
embedded for every element. Highlights are report overlays; they do not modify the tested page or
visual baselines. Missing, hidden or ambiguous elements retain their details with
an explicit explanation when a highlight cannot be captured.

These are candidate accessibility findings, not proof of a change from production.
Existing rule suppressions still apply and remain disclosed. Trial reports also
include element evidence, retaining each retry attempt separately. Share the
report directory with its images, not just the HTML file.

Older reports cannot recover details that were not saved. Run Shakedown again
with the updated version to capture this evidence.

## Choose the testing level and viewports

Start with application health, then expand the scope when you are ready:

```sh
npx shakedown regression --level=errors --viewports=desktop
npx shakedown regression --level=core --viewports=desktop
npx shakedown regression --level=full --viewports=desktop,tablet,mobile
```

| Level | What runs |
| --- | --- |
| `errors` | Candidate HTTP status, PHP/Twig error output, title presence, browser JS/console/request failures and broken/missing image sources; distortion and overflow advisories. No production requests, axe audit or paired comparison screenshots. |
| `core` | Application checks plus serious/critical axe findings, status/redirect comparisons, form definitions and empty-link differences. Accessibility findings retain element highlights. |
| `full` | Core checks plus all axe findings, title/heading/image/layout/structure differences, paired screenshots and advisory pixel diffs for matched pages. |

Levels are cumulative in check coverage. Core changes are review priorities, not
proof of a severe regression. Forms are inspected, never submitted; these levels
do not claim to test interactive workflows. All profiles retain existing route,
request and rule suppressions. Core reports disclose omitted minor/moderate axe
findings; the underlying axe engine may still evaluate those rules.

The **Review** filter narrows observations to broken candidate pages, serious
accessibility issues, behaviour changes, presentation/content changes, inconclusive observations,
or observations with no findings in the selected checks. **Viewport** further
narrows the report. Filters do not change totals, exit status or saved evidence.

Built-in viewport presets are desktop **1280×900**, tablet **768×1024** and mobile
**390×844**. These are Chromium viewport dimensions, not device/touch emulation.
Custom `viewports` entries override preset dimensions or add named sizes. Unknown
or duplicate names fail early. Selection order is preserved; feeds run once.

Save defaults inside the existing `regression` object:

```json
"defaultLevel": "full",
"coverage": "exhaustive",
"defaultViewports": ["desktop", "tablet", "mobile"],
"accessibility": "off"
```

Merge these settings into the theme's existing `shakedown.config.json`, inside
`regression`, keeping its reference and candidate URLs. This example deliberately
skips accessibility. Then run `npx shakedown regression` from the theme directory.
`--routes` remains a command-line-only focus option; it has no saved config key.

CLI flags override defaults for one run without editing configuration. New
`init` configurations select desktop, tablet and mobile. Existing configurations
retain their explicit `defaultViewports` or `viewports` selection. With neither
setting, all three presets are selected. The level defaults to full.

Each command creates a separate report. Limited runs prominently show omitted
checks; an errors-only pass does not mean the site passed full regression.
Candidate-only errors runs use the derived route matrix without the production
navigation supplement, and classify visited routes as `candidate-checked`.
The errors level still uses the regression environment configuration.

## Choose coverage or focus on a route

Testing level determines **which checks** run. Coverage determines **which routes**
are selected; viewports determine **which sizes** are captured. A full run is still
sampled unless you change coverage.

```sh
npx shakedown regression --level=full --coverage=exhaustive
```

Exhaustive adds eligible published public singles/pages and public taxonomy terms,
including empty terms which may have their own landing-page content. Route
exclusions still apply. It does not exhaustively test author/date archives,
pagination, arbitrary query combinations, remote-only content or interactions.
Inventory failure makes an exhaustive run incomplete; it cannot silently claim
complete coverage. Large inventories across three viewports can take considerably
longer than a sampled run.

The report counts known routes not selected and lists selected observations not
visited. Download `run.json` for the complete omitted-content inventory; HTML
retains the count and the command for exhaustive coverage.
An unavailable inventory means omitted-route counts are unknown. A page absent
from the tested observations has not passed.

To investigate one page quickly:

```sh
npx shakedown regression --level=full --viewports=desktop --routes=/training-type/health-service-modelling-associates-programme-hsma/
```

For several pages, use `--routes=/about/,/contact/`. These are exact paths, not
patterns. Quote the argument when a query contains shell characters, and encode
literal commas as `%2C`. Shakedown selects only discovered, allowed routes;
eligible inventoried content can be selected even when sampling would omit it.
Unknown, ignored or unsafe paths fail explicitly. Production homepage discovery
may still run to resolve navigation routes. The report discloses the focused
selection; remove `--routes` to restore broader coverage.

## Review repeated changes and screenshot diffs

**Repeated changes** groups identical recorded changes and recognised title
separator changes. It also groups source changes for images uniquely matched by
alt text with equal rendered dimensions, and repeated landmark role/label changes.
Ambiguous images are not guessed for grouping. Expand a group to open each route
and viewport. Grouping does not suppress findings or decide whether they are
improvements; other changes on the same pages still need review.

Image evidence omits equal comparison values using duplicate-aware matching,
so an insertion or reordering does not make every later image appear changed.
The remaining values are **changed or unmatched**, not assumed one-to-one pairs.
Source, alternative text, dimensions and rendering metadata remain reviewable.
Position-only image movement is described as layout evidence and kept out of the
image-content element panel; positions remain in raw JSON and visual comparisons.
Empty-link movement alone is not a content change. Legacy or mismatched evidence
arrays retain their full captured group with an explicit explanation.

**Repeated image advisories** groups identical image-distortion findings using
recorded messages, element targets, dimensions and suppression state. Each group
shows one representative and affected-route/observation counts. Different cases
remain separate. This reduces repeated footer-logo findings without changing
page verdicts or deleting raw evidence. Distortion is still a candidate advisory:
it does not yet classify a finding as new, unchanged, worsened or resolved
relative to production.

**Technical evidence** shows unmatched values rather than full arrays of unchanged
members. Use `run.json` for complete evidence. Acceptance scope is explained once
under **Accepting intentional differences**; each finding links there and supplies
its config snippet. Transport panels are omitted when there are no restrictions,
advisories or capture limitations; successful lazy-loading bookkeeping remains in
raw evidence. These presentation changes do not alter checks, counts or exit codes.

In full runs, **Appearance changed** links to Playwright's visual comparison
viewer for matched pages. Inspect the reference, candidate and highlighted diff
at a readable scale. Shakedown uses Playwright's `toMatchSnapshot` comparator
with a colour threshold of 0.2 and zero allowed differing pixels. Current reports
do not use the older changed-area percentage or 16/255 channel tolerance.
Font rendering, moving content and capture timing can still contribute; a
mismatch requests review rather than diagnosing its cause or severity.

Screenshots retain the selected viewport width while capturing the full document
height. Shakedown does not inject overflow styles or remove off-screen elements.
Horizontal scrolling is checked separately. Large document dimensions or an
element positioned outside the viewport alone do not establish a defect: root
clipping and ancestor overflow clipping are considered before suggesting
contributors. The raw JSON retains document/viewport widths, measured horizontal
scroll range and element positions. Suggested contributors are not proven causes.

Diffs exceeding 16 million pixels, missing captures and decoding failures are
disclosed as unavailable. Originals remain available when captured. Screenshot
changes request review but do not themselves fail the process exit code or create
baselines. Existing saved reports remain as generated; new runs use the updated
report format from the installed Shakedown version.
