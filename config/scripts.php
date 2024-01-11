<?php

/**
 * Scripts Configuration
 *
 * This configuration file specifies JavaScript scripts to be registered and enqueued in the WordPress theme.
 * Each script is defined with a set of parameters that align with the wp_register_script function in WordPress,
 * along with additional PressGang-specific parameters for enhanced control.
 *
 * Standard wp_register_script parameters:
 * - 'handle': (string) The script's handle.
 * - 'src': (string) The script's source URL.
 * - 'deps': (array) An array of registered script handles this script depends on.
 * - 'ver': (string) The script's version number.
 * - 'in_footer': (bool) Whether to enqueue the script before </body> instead of in the <head>.
 *
 * Additional PressGang-specific parameters:
 * - 'hook': (string) The action hook to use for enqueuing the script (default: 'wp_enqueue_scripts').
 * - 'async': (bool) If set to true, adds 'async' attribute to the script tag.
 * - 'defer': (bool) If set to true, adds 'defer' attribute to the script tag.
 *
 * Example configuration array:
 * return [
 *     'main' => [
 *         'src' => get_stylesheet_directory_uri() . '/js/dist/your-script.js',
 *         'deps' => ['jquery'],
 *         'ver' => '1.0.0',
 *         'in_footer' => true,
 *         'hook' => 'wp_enqueue_scripts', // Optional; default is 'wp_enqueue_scripts'
 *         'async' => false,
 *         'defer' => true
 *     ],
 *     ... additional scripts ...
 * ];
 *
 * @var array
 */

return [];
