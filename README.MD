<img src="https://github.com/pressgang-wp/pressgang/blob/master/img/pressgang-banner.png" alt="PressGang">

# PressGang: Hijacking Your WordPress Development Workflow

**Modernize your WordPress development and ship your WordPress themes faster than ever. PressGang anchors your workflow in modern development practices. Build cleaner, faster, smarter themes and navigate your development course to calmers seas!**

## Overview

PressGang is a powerful and flexible WordPress parent theme framework designed to streamline theme development and enhance customization capabilities. As a parent theme framework, PressGang serves as a foundation upon which child themes can be built, allowing developers to create custom themes more efficiently while inheriting the robust features and structure of the PressGang parent theme.

### Key Features

1. **Rapid Development:** Engineered for quickly building WordPress themes with clean and modern coding standards, accelerating the development process by providing a solid foundation and tools.
2. **Timber Integration:** Utilizes the [Timber library](https://upstatement.com/timber/), separating template code from PHP logic through the Twig templating engine, resulting in cleaner, more maintainable code.
3. **MVC-inspired Architecture:** Introduces the concept of Controllers to WordPress theme development, expanding on the Model-View-Controller (MVC) approach for better organization and separation of concerns.
4. **Modern PHP Practices:** Emphasizes Configuration over Convention, inspired by frameworks like Laravel, allowing for bootstrapping repetitive WordPress tasks via configuration files, reducing boilerplate code, and increasing developer productivity.
5. **Composer and PSR-4:** Utilizes Composer for dependency management and adheres to PSR-4 coding standards for autoloading, ensuring a consistent and modern code structure.
6. **Flexibility and Customization:** Maintains the core WordPress structure, allowing developers to leverage their existing WordPress knowledge while benefiting from additional features. It provides powerful tools and conventions but remains flexible for direct interaction with WordPress as needed.

By leveraging these features, PressGang empowers developers to create sophisticated, performant, and maintainable WordPress themes with greater ease and efficiency.

### Prerequisites
To make the most out of PressGang, familiarity with the following tools is recommended:
* [Timber](http://upstatement.com/timber/)
* [Twig](http://twig.sensiolabs.org/documentation)
* [Composer](https://getcomposer.org/)
* [Grunt](http://gruntjs.com/)

## Getting Started

PressGang is designed as a WordPress _parent theme_ that acts as a library for your [_child theme_](https://codex.wordpress.org/Child_Themes). To get started, you will need to create a child theme.

### Quick Start with PressGang-Child

The fastest way to get up and running is by using our [pressgang-child](https://github.com/pressgang-wp/pressgang-child) repository, which includes a [grunt-init template](http://gruntjs.com/project-scaffolding) for creating a PressGang-ready child theme.

To get started:

1. Clone the pressgang-child repository:
    ```bash
    git clone https://github.com/pressgang-wp/pressgang-child your-theme-name
    ```

2. Navigate into your new theme directory:
    ```bash
    cd your-theme-name
    ```

3. Follow the instructions in the README to set up your environment and start developing your child theme.

### Manual Setup

If you prefer to start from scratch, you can manually set up your PressGang environment.

1. Clone the PressGang repository:
    ```bash
    git clone https://github.com/pressgang-wp/pressgang
    ```

2. Create your own child theme by following the [WordPress guidelines for child themes](https://codex.wordpress.org/Child_Themes).

### Composer Installation

You can also include PressGang as a dependency in your project using Composer.

1. Require the PressGang package via Composer:
    ```bash
    composer require pressgang-wp/pressgang
    ```

2. Create and configure your child theme to extend the PressGang parent theme.

For detailed instructions on setting up and configuring your child theme, refer to the [PressGang documentation](https://github.com/pressgang-wp/pressgang).

## License

PressGang is open-sourced software licensed under the [MIT license](LICENSE.md).
