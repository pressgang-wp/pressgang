---
description: >-
  A WP-CLI command package that scaffolds, configures, and packages
  PressGang WordPress themes. Dry-run by default — preview every plan
  before it writes a byte.
---

# 🛞 Capstan

Aboard ship, the capstan is the winch that hauls the anchor and gets the vessel underway. In your project, Capstan gets a new PressGang theme underway — WordPress core, parent theme, child theme, one command. ⚓

{% hint style="success" %}
Capstan installs as a **global WP-CLI package**, so it's available *before* any project exists — exactly when you need scaffolding. Nothing to `composer require` into a project that isn't there yet.
{% endhint %}

## 📦 Install

{% code title="Terminal" %}
```bash
wp package install https://github.com/pressgang-wp/pressgang-capstan.git
```
{% endcode %}

**Requirements:** PHP 8.3+, [WP-CLI](https://wp-cli.org/), [Composer](https://getcomposer.org/)

{% hint style="warning" %}
WP-CLI's shorthand package index (`wp package install pressgang-wp/capstan`) is deprecated and may not resolve the package — use the Git URL above.
{% endhint %}

---

## 🧰 Commands

| Command | Description |
|---|---|
| `wp capstan new` | Scaffold a full PressGang project — core, parent, child theme |
| `wp capstan make child` | Scaffold a child theme into an existing WordPress install |
| `wp capstan make cpt` | Scaffold a custom post type entry in `config/custom-post-types.php` |
| `wp capstan make block` | Scaffold an ACF block — block.json, Twig stub, config registration |
| `wp capstan make controller` | Scaffold a controller with a documented `$context_getters` manifest |
| `wp capstan resolve <url>` | Template hierarchy candidates → resolved controller for a URL |
| `wp capstan context <Ctrl>` | A controller's context manifest and getters; `--add` publishes keys |
| `wp capstan config dump` | The resolved PressGang configuration the theme boots with |
| `wp capstan snippets` | Registered snippets, their resolved classes and args |
| `wp capstan doctor` | Deterministic theme configuration health checks |
| `wp capstan theme package` | Build a WordPress-uploadable ZIP from a theme directory |
| `wp capstan about` | Capstan version, PHP version, WordPress root detection |

## 🧭 Introspection

PressGang's conventions are invisible by design — introspection makes them verifiable:

{% code title="Terminal" %}
```bash
wp capstan resolve /events/
# Hierarchy candidates, the controller each would infer, and the winner:
# dispatch — EventsController renders archive-event.twig (via "archive-event")

wp capstan context FrontPage
# The $context_getters manifest, each getter's declaring class, and
# theme getters not yet published in the manifest.

wp capstan context FrontPage --add=news,events --force
# Publishes keys into the manifest — explicit selection, theme-owned
# files only, lint-checked before the file is replaced.

wp capstan doctor
# 11 deterministic checks: autoload, namespace, snippet/provider/route
# classes, shadowed page templates, legacy v1 boot files...
```
{% endcode %}

These answer the questions agents (and new crew) ask most: *which controller handles this URL, what data does the template get, and is the rigging sound?* [Bosun](BOSUN.md)-composed guidelines teach agents these recipes.

{% hint style="info" %}
**Dry-run by default.** Every scaffolding command prints its execution plan first and only writes when you re-run with `--force`. Review the charts before you sail. 🗺️
{% endhint %}

---

## 🚀 New Project

One command takes you from empty directory to running PressGang project: downloads WordPress core, installs the parent theme via Composer, scaffolds a child theme, and optionally configures ACF Pro as an MU-plugin.

{% tabs %}
{% tab title="Quick start" %}
{% code title="Terminal" %}
```bash
# Preview the execution plan
wp capstan new my-theme --dbuser=root

# Looks good? Execute it
wp capstan new my-theme --dbuser=root --force

# With ACF Pro wired as an MU-plugin
wp capstan new my-theme --dbuser=root --acf --force
```
{% endcode %}
{% endtab %}

{% tab title="Full customisation" %}
{% code title="Terminal" %}
```bash
wp capstan new my-theme \
  --dbname=mytheme --dbuser=wp --dbpass=secret --dbhost=localhost \
  --url=http://mytheme.local --title="My Theme" \
  --admin-user=admin --admin-email=dev@example.com \
  --namespace=MyTheme --acf --force
```
{% endcode %}
{% endtab %}
{% endtabs %}

{% hint style="info" %}
With `--acf`, the root `composer.json` and MU-plugin loader are written, but ACF Pro itself is not downloaded — it needs licence authentication. The summary output lists the steps to complete the install.
{% endhint %}

---

## 🧒 Child Theme

Scaffold a PressGang child theme into an existing WordPress installation — PSR-4 `src/`, `config/` registration, Composer wiring, all from the maintained starter template.

{% code title="Terminal" %}
```bash
# Preview what would be generated
wp capstan make child my-theme

# Generate it
wp capstan make child my-theme --force

# Custom display name and namespace
wp capstan make child my-theme --name="My Theme" --namespace=MyTheme --force

# Explicit target path
wp capstan make child my-theme --path=/srv/www/wp-content/themes --force
```
{% endcode %}

After a scaffold's dependencies land, Capstan automatically runs [Bosun](BOSUN.md) (when installed) — so a freshly launched theme starts life with its AI crew already briefed. 🧭

---

## 📦 Theme Packaging

Build a ZIP ready for **Appearance → Themes → Upload Theme**. Build artifacts — `.git/`, `node_modules/`, editor directories, `.env`, dev config — are excluded automatically.

{% code title="Terminal" %}
```bash
# Preview what would be packaged (from inside a theme directory)
wp capstan theme package

# Create the zip
wp capstan theme package --force

# Package a specific directory, custom output path
wp capstan theme package /path/to/my-theme --output=release/my-theme.zip --force
```
{% endcode %}

The ZIP lands alongside the theme directory by default (e.g. `themes/my-theme.zip`).

---

## 🧠 Philosophy

| | Principle |
|---|---|
| 🗺️ | **Dry-run by default** — always preview before writing |
| 🎯 | **Explicit over implicit** — no hidden global state |
| 🔍 | **Minimal abstractions, maximum inspectability** |
| 🌍 | **Global by design** — available before any project exists |

## 🗺️ Roadmap

Capstan's [README roadmap](https://github.com/pressgang-wp/pressgang-capstan#roadmap) is the single source of truth for planned commands. Currently charted:

| | Planned | Purpose |
|---|---|---|
| 🖼️ | `theme screenshot` | Generate a theme screenshot |

---

## 🔗 Links

- [GitHub: pressgang-wp/pressgang-capstan](https://github.com/pressgang-wp/pressgang-capstan)

Heave away, and get her underway. 🛞⚓
