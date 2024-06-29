<?php

/**
 * Sidebars Configuration
 *
 * Defines an array of sidebars (widget areas) to be registered in the WordPress theme.
 * Each entry in the array represents the configuration for one sidebar, using the parameters
 * accepted by the WordPress function register_sidebar.
 *
 * Configuration Options:
 * - 'id': (string) A unique identifier for the sidebar.
 * - 'name': (string) Name of the sidebar displayed in the WordPress admin.
 * - 'description': (string) Description of the sidebar, shown in the admin.
 * - 'class': (string) CSS class to assign to the sidebar.
 * - 'before_widget': (string) HTML content to prepend to each widget in the sidebar.
 * - 'after_widget': (string) HTML content to append to each widget in the sidebar.
 * - 'before_title': (string) HTML content to prepend to each widget title.
 * - 'after_title': (string) HTML content to append to each widget title.
 *
 * Usage Example:
 * return [
 *   'main-sidebar' => [
 *      'id' => 'main-sidebar',
 *      'name' => __("Main Sidebar", THEMENAME),
 *      'description' => __("A primary widget area", THEMENAME),
 *      'class' => 'sidebar',
 *      'before_widget' => '<li id="%1$s" class="widget %2$s">',
 *      'after_widget' => '</li>',
 *      'before_title' => '<h2 class="widgettitle">',
 *      'after_title' => '</h2>',
 *   ],
 *   ... additional sidebars ...
 * ];
 *
 * @see https://developer.wordpress.org/reference/functions/register_sidebar/
 * @see https://codex.wordpress.org/Sidebars
 *
 * @var array
 */

return [];
