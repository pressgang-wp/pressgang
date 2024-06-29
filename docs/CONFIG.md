# Config

## Centralized Configuration Management

PressGang adopts a centralized approach to configuration, storing settings in dedicated files within the `config` directory. This structure simplifies the management and updating of theme settings, ensuring a clean and organized codebase.

### How It Works

1. **Config Files:** Individual PHP files within the `config` directory contain associative arrays that define settings for various theme components.
2. **Loading and Configuration:** Loading of `config` files is handled in the `Bootstrap` namespace via the `FileConfigLoader` and `ConfigLoaderInterface` used to read and merge the configuration settings, supporting hierarchical overrides (e.g., child theme settings overriding parent theme settings). The `Configuration` namespace provides singleton classes that take the config settings and apply the necessary logic to them.
3. **Central Access Point:** The `Config` class provides static methods to retrieve settings, ensuring a single point of access and enabling caching for performance.

### Benefits for Theme Development

* **Streamlined Workflow:** By adhering to a convention over configuration philosophy, PressGang reduces the overhead associated with setting up and maintaining theme configurations.
* **Enhanced Maintainability:** The clear separation of configuration concerns into dedicated files makes the codebase easier to navigate and maintain.
* **Flexibility:** Developers can easily extend and customize the theme by adding new configuration files or modifying existing ones without affecting the overall structure.

This documentation should provide a comprehensive understanding of the configuration approach in PressGang and how it supports efficient and maintainable theme development. For further details, refer to the PressGang GitHub repository.

## Config Files

All files are present in the `config` folder of the PressGang theme. These can be overridden and modified to uniquely configure your child theme by following the same directory structure.

`acf-options.php`  
Manages Advanced Custom Fields (ACF) options.

`actions.php`  
Handles custom actions within the theme.

`block-categories.php`  
Registers custom block categories for the Gutenberg editor.

`block-patterns.php`  
Defines and registers block patterns.

`blocks.php`  
Manages block registration for Gutenberg.

`color-palette.php`  
Configures custom color palettes for the theme.

`context-managers.php`  
Manages context providers for Timber.

`custom-post-types.php`  
Registers and configures custom post types.

`custom-taxonomies.php`  
Defines and registers custom taxonomies.

`dequeue-styles.php`  
Handles dequeueing of styles.

`deregister-scripts.php`  
Manages deregistration of scripts.

`menus.php`  
Registers navigation menus.

`meta-tags.php`  
Manages meta tag configurations.

`nodes.php`  
Handles custom node configurations.

`plugins.php`  
Manages plugin-related configurations.

`query-vars.php`  
Registers custom query variables.

`remove-menus.php`  
Configures removal of specific menus.

`remove-nodes.php`  
Manages removal of custom nodes.

`remove-support.php`  
Handles removal of theme support features.

`routes.php`  
Configures custom routes.

`scripts.php`  
Registers and manages scripts.

`shortcodes.php`  
Manages shortcode registrations.

`sidebars.php`  
Registers widget sidebars.

`snippets.php`  
Manages code snippets and includes.

`styles.php`  
Registers and manages styles.

`support.php`  
Adds theme support features.

`twig-extensions.php`  
Configures Twig extensions for Timber.

`widgets.php`  
Manages widget registrations.

## Example usage:

Here is an example of registering a custom post type via the config. The associative array arguments match the `register_post_type` args.

### `custom-post-types.php`
```php

return [
    'event' => [
        'label' => 'Events',
        'description' => 'A custom post type for events.',
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-calendar',
        'supports' => ['title', 'editor', 'excerpt', 'custom-fields'],
        'taxonomies' => ['category', 'post_tag'],
        'rewrite' => [
            'slug' => 'events',
            'with_front' => false
        ],
        'show_in_rest' => true,
    ],
];

```
