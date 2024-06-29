<?php

/**
 * Styles Configuration
 *
 * Defines an array of stylesheets to be registered and enqueued in the WordPress theme.
 * Each array entry specifies the configuration for a single stylesheet.
 *
 * Configuration Options:
 * - 'handle': (string) The handle name for the stylesheet.
 * - 'src': (string) URL to the stylesheet.
 * - 'deps': (array) An array of registered stylesheet handles this stylesheet depends on.
 * - 'ver': (string) String specifying the stylesheet version number.
 * - 'media': (string) The media for which this stylesheet has been defined. Default 'all'.
 * - 'hook': (string) The WordPress action hook on which the stylesheet is enqueued. Default 'wp_enqueue_scripts'.
 *   Additional PressGang parameters can include 'preconnect' for performance optimization.
 *
 * Usage Example:
 * return [
 *   'main-style' => [
 *      'handle' => 'main-style',
 *      'src' => get_stylesheet_directory_uri() . '/css/main.css',
 *      'deps' => ['bootstrap'],
 *      'ver' => '1.0.0',
 *      'media' => 'all',
 *      'hook' => 'wp_enqueue_scripts',
 *      'preconnect' => 'https://fonts.googleapis.com',
 *   ],
 *   ... additional styles ...
 * ];
 *
 * @see https://developer.wordpress.org/reference/functions/wp_register_style/
 *
 * @var array
 */

return [];
