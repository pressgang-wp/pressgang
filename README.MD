<img src="https://github.com/pressgang-wp/pressgang/blob/master/img/pressgang-banner.png" alt="PressGang">

# Hijacking Your WordPress Development Workflow

**Ahoy there! Modernize your WordPress development and ship your WordPress themes faster than ever. PressGang anchors your workflow in modern development practices. Build cleaner, faster, smarter themes and navigate your development course to calmer seas.**

📖 **[Read the full documentation at docs.pressgang.dev](https://docs.pressgang.dev/)**

## Overview

PressGang is a WordPress parent theme framework that gives you the architecture of a modern PHP application — Composer autoloading, Twig templating via Timber, config-driven bootstrapping, and a clear separation of concerns — without leaving the WordPress ecosystem. Build child themes on a solid, opinionated foundation and spend your time on features, not plumbing.

## ✨ Key Features

- 🚀 **Rapid Development** — A solid foundation and conventions that accelerate theme development with clean, modern PHP.
- 🌲 **Timber + Twig** — Built on [Timber](https://timber.github.io/docs/v2/) to separate template markup from PHP logic through the [Twig](https://twig.symfony.com/) templating engine.
- 🎮 **Controller Architecture** — MVC-inspired controllers act as view models, preparing context for templates with a clear separation of concerns.
- ⚓ **Convention over Configuration** — Inspired by frameworks like Laravel, repetitive WordPress tasks are bootstrapped via declarative config files — less boilerplate, more building.
- 📦 **Composer & PSR-4** — Dependency management via [Composer](https://getcomposer.org/) and PSR-4 autoloading for a consistent, modern codebase.
- 🔧 **Flexible by Design** — Keeps the core WordPress structure intact. Leverage everything you already know while gaining powerful tools and conventions.

## 🧭 Core Concepts

### ⚙️ Config-Driven Bootstrapping

Everything in PressGang starts with a config file. Arrays in `config/` declare what gets registered — post types, taxonomies, sidebars, blocks, scripts, styles, and more. Each config file maps to a class in `src/Configuration/` by convention (e.g. `config/sidebars.php` → `Sidebars`). No queries, no side effects — just declarative registration.

### 🎮 Controllers

Controllers live in `src/Controllers/` and are **view models**, not request handlers. Each controller extends `AbstractController`, gathers data via Timber, builds a context array, and selects a template. They must be side-effect free — no writes, no remote requests, no direct rendering.

### 🌍 Context Managers

Context managers enrich the global `Timber::context()` with data needed across many templates — menus, site info, theme mods, ACF option pages. They implement `ContextManagerInterface`, are registered via `config/context-managers.php`, and must be safe to run on every request (frontend, admin, AJAX, CLI).

### 🧩 Twig Extensions

All custom Twig functions, filters, and globals are registered through extension managers configured in `config/twig-extensions.php`. Twig remains a presentation-only layer — no database queries, no mutations, no business logic.

### 🧱 Blocks & Snippets

Gutenberg blocks are declared in `config/blocks.php` and live under `blocks/<block-name>/` with a `block.json` and Twig template. Snippets are reusable view partials registered via `config/snippets.php` — self-contained, explicitly parameterised UI components.

## 📋 Requirements

- **PHP** 8.3+
- **WordPress** 6.4+
- **Timber** 2.0+
- **Composer**

## 🚀 Getting Started

PressGang is a WordPress _parent theme_ that acts as a framework for your [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/).

### Quick Start with PressGang-Child

The fastest way to weigh anchor is with the [pressgang-child](https://github.com/pressgang-wp/pressgang-child) starter repo:

```bash
git clone https://github.com/pressgang-wp/pressgang-child your-theme-name
cd your-theme-name
composer install
```

Then follow the instructions in that repo's README to configure your child theme.

### Manual Setup

1. Clone PressGang into your `wp-content/themes/` directory:
    ```bash
    git clone https://github.com/pressgang-wp/pressgang
    ```

2. Create your own [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/) that extends PressGang.

### Composer Installation

Require PressGang as a dependency in your child theme:

```bash
composer require pressgang-wp/pressgang
```

## 🧪 Testing

```bash
composer install          # install dev dependencies
composer test             # run the unit test suite
```

Tests use PHPUnit + BrainMonkey — no WordPress installation required. See the [Testing docs](https://docs.pressgang.dev/TESTING) for details on writing new tests.

## 📖 Documentation

Uncover the buried treasure at: **[docs.pressgang.dev](https://docs.pressgang.dev/)**

## 📄 License

PressGang is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
