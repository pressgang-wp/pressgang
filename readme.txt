=== PressGang Parent Theme ===
Contributors: benedict-w
Requires at least: WordPress 6.4
Tested up to: WordPress 6.4.3
Requires PHP: 8.3
Version: 2.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT
Tags: responsive-layout, accessibility-ready, custom-background, custom-logo, custom-menu, featured-images, theme-options, threaded-comments, translation-ready
Text Domain: PressGang

== Description ==
PressGang is a modern WordPress parent theme framework designed to accelerate custom theme development while keeping code clean, explicit, and maintainable.

PressGang explicitly avoids being a page builder or abstraction-heavy framework. It delivers:

* A clean, opinionated foundation for custom themes
* Clear separation between PHP logic and Twig templates
* Modern development workflow built on Composer and namespaces
* Minimal "magic," prioritising clarity, performance, and developer control

Key features:

* Parent theme framework extended via child themes
* Timber/Twig integration for logic-presentation separation
* MVC-inspired organisation using controllers
* Composer-based dependency management with PSR-4 autoloading (`PressGang\` -> `src/`)
* Configuration-driven approach to reduce boilerplate

Explore more at https://github.com/pressgang-wp/pressgang.

== Installation ==
1. In your WordPress dashboard, go to Appearance > Themes and click the 'Add New' button.
2. Click 'Upload Theme' and Choose File, then select the theme's .zip file. Click 'Install Now'.
3. Click 'Activate' to use your new theme right away.

== Requirements ==
* PHP: 8.3+
* WordPress: 6.4+
* Timber: 2.0+

== Changelog ==
= 2.0.0 =
* Major architectural refactor from legacy codebase
* PSR-4 autoloading with structured `src/` codebase
* Timber 2.0+ compatibility
* Composer-based dependency management
* Refactored Snippets architecture
* Configuration-driven bootstrapping via `config/`
* Controller-based template rendering

= 1.0.0 =
* Retrospective tag marking the legacy fork baseline

== Upgrade Notice ==
= 2.0.0 =
Major version reflecting architectural changes. New projects should use v2.x; legacy implementations are unsupported.
