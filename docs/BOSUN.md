---
description: >-
  A WP-CLI package that composes AI agent guidelines and skills for
  PressGang themes — generated from what each theme actually has installed
  and enabled.
---

# 🧭 Bosun

Aboard ship, the bosun pipes the captain's orders to the crew. In your project, Bosun pipes PressGang's conventions to the AI crew — so every agent that comes aboard already knows the ropes. ⚓

PressGang's best features are its most hidden: [template routing](TEMPLATE-ROUTING.md), context getter manifests, config-driven registration, [Quartermaster](QUARTERMASTER.md), the snippets library. By design they leave barely a trace in a child theme's code — wonderful for developers who know the ship, invisible to agents who don't. Left unbriefed, an agent writes stub files, hand-rolled `WP_Query` arrays, and `functions.php` hooks: working code that misses everything that makes PressGang worth sailing.

Bosun is the briefing.

## 📦 Install

{% code title="Terminal" %}
```bash
wp package install https://github.com/pressgang-wp/pressgang-bosun.git
```
{% endcode %}

One global install briefs every theme on the machine.

## 🚀 Usage

{% code title="Terminal" %}
```bash
wp bosun install    # compose CLAUDE.md + AGENTS.md for the active child theme
wp bosun update     # recompose after composer updates (idempotent)
```
{% endcode %}

That's it — all hands briefed. Commit the generated files so agents on machines without Bosun still get the briefing.

## 🧠 What Gets Composed

The document always opens with an **inventory of reality** — installed package versions with lock refs, and the feature opt-ins detected in `config/` — so agents reason about what the theme actually runs, not the ecosystem's newest ideas:

* Guidance for [Template Routing](TEMPLATE-ROUTING.md) only comes aboard when `config/service-providers.php` registers the provider.
* [Quartermaster](QUARTERMASTER.md) guidance appears only when the package is installed — along with a pointer to its machine-readable API index (`docs/api-index.json`, every method signature and the WP args it sets).
* Skills (Agent Skills format) install to `.claude/skills/` — including a v1 → v2 migration skill that appears **only** on themes still booting through PressGang v1, and disappears once they're migrated.

## 🧩 Where Guidance Comes From

Three tiers, later tiers overriding earlier ones:

| | Tier | Location |
|---|---|---|
| 📦 | Package-shipped | `{package}/resources/bosun/guidelines/**.md` |
| ⚓ | Bosun built-ins | a frozen baseline for packages that predate shipped fragments |
| 🏠 | Theme-local | `{theme}/.ai/guidelines/**.md` — your house rules and overrides |

{% hint style="success" %}
**Bosun never clobbers your files.** It owns only the region between `<!-- bosun:start -->` and `<!-- bosun:end -->`. A hand-written `CLAUDE.md` keeps every byte and gains the region appended at the end; re-runs replace the region in place.
{% endhint %}

Customise in `.ai/guidelines/` (or outside the region) — never inside the region, which is replaced on every run.

## ⚓ With Capstan

[Capstan](https://github.com/pressgang-wp/pressgang-capstan) scaffolds run `wp bosun install` automatically after a new theme's dependencies land, so a freshly launched theme starts life with its crew already briefed.

***

Source & issues: [pressgang-wp/pressgang-bosun](https://github.com/pressgang-wp/pressgang-bosun)
