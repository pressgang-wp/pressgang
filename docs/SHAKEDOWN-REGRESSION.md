---
description: >-
  Install Shakedown in your project, compare an updated site with production,
  and review paired desktop and mobile evidence.
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
screenshots at desktop and mobile sizes. Feeds receive HTTP checks only. This is
representative derived coverage, not an exhaustive crawl of every post.

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
2. **Expand Route index.** Select a path and viewport to jump to its evidence.
3. **Read Candidate correctness.** This lists HTTP/rendering, JavaScript, asset
   and accessibility failures independently of how production behaves.
4. **Expand Differences.** Review changed titles/headings, landmarks, forms,
   empty links/headings, image dimensions and content structure.
5. **Compare the screenshots.** Reference is production; candidate is the updated
   site. Click a thumbnail to view the full-size capture.
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

A difference count is **not a defect count**. For example, a shorter local image
width with an unchanged height can expose stretching, but image-proportion defects
are not yet a named automatic blocking check. Review the dimensions and paired
screenshots. Likewise, an empty button destination may come from missing local
content rather than a code change. Production is a reference, not proof of correctness.

## 5. Fix, rerun and share

After fixing code or correcting local content, run the same command again. Each
invocation creates a new `run-…` directory, so previous evidence remains available.
There are no automatic retries within a run. Old run folders are not automatically
removed; keep the evidence you need and delete older generated folders yourself.

Zip the **whole run directory** to share it. It contains `index.html`, paired PNGs,
`run.json`, `plan.json` and the discovery matrix. Sending the HTML file alone loses
the screenshots. Keep sandbox baselines in `tests/__screenshots__/` separate from
these generated reference/candidate captures.

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

## Other settings and limits

| Regression setting | Default / purpose |
| --- | --- |
| `viewports` | Desktop 1280×900 and mobile 390×844; entries contain `name`, `width`, `height`. |
| `navigationLimit` | 40 production-navigation links, bounded to 0–200; zero disables that supplement. |
| `timeout` | 20,000 ms per operation, configurable from 1,000–120,000 ms. |
| `criticalRoutes` | Extra paths to supplement the derived matrix, never replace it. |
| `defaultReference` / `defaultCandidate` | `production` / `local`. |

Forms are inspected but not submitted. Menu/filter interaction journeys and pixel
equality gates are outside this first regression mode. ACF schema helps identify
representative surfaces but does not establish that every relationship or rendered
component should be populated; complete ACF coverage is not claimed. The runner
does not control WordPress/plugin side effects of serving GET requests or booting
WP-CLI.

For implementation details, see [Design & Internals](SHAKEDOWN-DESIGN.md).
