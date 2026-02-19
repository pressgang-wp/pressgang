---
description: >-
  PressGang anchors your workflow in modern development practices. Build
  cleaner, faster, smarter themes and navigate your development course to
  calmer seas!
---

# PressGang

## Overview

PressGang is a powerful and flexible WordPress parent theme framework designed to streamline theme development and enhance customization capabilities. As a parent theme framework, PressGang serves as a foundation upon which child themes can be built, allowing developers to create custom themes more efficiently while inheriting the robust features and structure of the PressGang parent theme.

Think of PressGang as your ship's hull — it handles the heavy structural work so your child theme can focus on what makes your site unique.

### Key Features

1. **Rapid Development:** Engineered for quickly building WordPress themes with clean and modern coding standards, accelerating the development process by providing a solid foundation and tools.
2. **Timber Integration:** Utilizes the [Timber library](https://timber.github.io/docs/v2/), separating template code from PHP logic through the Twig templating engine, resulting in cleaner, more maintainable code.
3. **MVC-inspired Architecture:** Introduces the concept of Controllers to WordPress theme development, expanding on the Model-View-Controller (MVC) approach for better organization and separation of concerns.
4. **Convention over Configuration:** Inspired by frameworks like Laravel, PressGang lets you bootstrap repetitive WordPress tasks via configuration files, reducing boilerplate code and increasing developer productivity.
5. **Composer and PSR-4:** Utilizes Composer for dependency management and adheres to PSR-4 autoloading standards (`PressGang\` -> `src/`), ensuring a consistent and modern code structure.
6. **Flexibility and Customization:** Maintains the core WordPress structure, allowing developers to leverage their existing WordPress knowledge while benefiting from additional features. It provides powerful tools and conventions but remains flexible for direct interaction with WordPress as needed.

By leveraging these features, PressGang empowers developers to create sophisticated, performant, and maintainable WordPress themes with greater ease and efficiency.

### Prerequisites

{% hint style="info" %}
To make the most out of PressGang, familiarity with the following tools is recommended
{% endhint %}

* [Timber](https://timber.github.io/docs/v2/)
* [Twig](https://twig.symfony.com/doc/3.x/)
* [Composer](https://getcomposer.org/)

### Requirements

* PHP 8.3+
* WordPress 6.4+
* Timber 2.0+

## Getting Started

PressGang is designed as a WordPress _parent theme_ that acts as a library for your [_child theme_](https://developer.wordpress.org/themes/advanced-topics/child-themes/).

{% hint style="success" %}
To get started, you will need to create a child theme. All hands on deck!
{% endhint %}

{% tabs %}
{% tab title="Quick Start" %}
The fastest way to get up and running is by using our [pressgang-child](https://github.com/pressgang-wp/pressgang-child) repository, which provides a ready-made child theme scaffold.

{% stepper %}
{% step %}
### Clone the repository

{% code title="Terminal" %}
```bash
git clone https://github.com/pressgang-wp/pressgang-child your-theme-name
```
{% endcode %}
{% endstep %}

{% step %}
### Navigate into your theme

{% code title="Terminal" %}
```bash
cd your-theme-name
```
{% endcode %}
{% endstep %}

{% step %}
### Install dependencies

{% code title="Terminal" %}
```bash
composer install
```
{% endcode %}
{% endstep %}

{% step %}
### Start developing

Follow the instructions in the README to set up your environment and start developing your child theme.
{% endstep %}
{% endstepper %}
{% endtab %}

{% tab title="Manual Setup" %}
If you prefer to start from scratch, you can manually set up your PressGang environment.

{% stepper %}
{% step %}
### Clone PressGang

{% code title="Terminal" %}
```bash
git clone https://github.com/pressgang-wp/pressgang
```
{% endcode %}
{% endstep %}

{% step %}
### Create your child theme

Create your own child theme by following the [WordPress guidelines for child themes](https://developer.wordpress.org/themes/advanced-topics/child-themes/).
{% endstep %}
{% endstepper %}
{% endtab %}

{% tab title="Composer" %}
You can also include PressGang as a dependency in your project using Composer.

{% stepper %}
{% step %}
### Require the package

{% code title="Terminal" %}
```bash
composer require pressgang-wp/pressgang
```
{% endcode %}
{% endstep %}

{% step %}
### Configure your child theme

Create and configure your child theme to extend the PressGang parent theme. For detailed instructions, refer to the [PressGang documentation](https://github.com/pressgang-wp/pressgang).
{% endstep %}
{% endstepper %}
{% endtab %}
{% endtabs %}

## License

PressGang is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
