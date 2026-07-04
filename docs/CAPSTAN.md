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
| `wp capstan theme package` | Build a WordPress-uploadable ZIP from a theme directory |
| `wp capstan about` | Capstan version, PHP version, WordPress root detection |

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
| 🧱 | `make block` / `make cpt` | Scaffold blocks and custom post types with config registration |
| 🧭 | `resolve <url>` | Show the template candidates and resolved controller for a URL |
| ⚙️ | `config dump` | Display the resolved PressGang configuration |
| ✂️ | `snippets` | List registered snippets and their constructor args |
| 📦 | `context <Controller>` | Show a controller's context keys and getters |
| 🩺 | `doctor` | Diagnose common theme configuration issues |
| 🖼️ | `theme screenshot` | Generate a theme screenshot |

The introspection commands are designed for humans and AI agents alike — once they ship, [Bosun](BOSUN.md) fragments will teach agents the recipes.

---

## 🔗 Links

- [GitHub: pressgang-wp/pressgang-capstan](https://github.com/pressgang-wp/pressgang-capstan)

Heave away, and get her underway. 🛞⚓
