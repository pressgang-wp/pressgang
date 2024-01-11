<?php

/**
 * Custom Routes Configuration
 *
 * This configuration file defines custom routes for the WordPress site using Timber's Routes library.
 * Each entry in the array represents a custom route, where the key is the route pattern and the value
 * is the path to the corresponding template file, which can be either a PHP or Twig file.
 *
 * The route pattern can include variables (e.g., '/my-route/:name'), which will be passed to the template.
 *
 * Example configuration array:
 * return [
 *     '/my-custom-route/' => 'path/to/template.php',     // PHP template
 *     '/another-route/' => 'path/to/another-template.twig', // Twig template
 *     ... additional routes ...
 * ];
 *
 * Note: Ensure that the template files exist in the specified paths and are compatible with Timber's routing system.
 *
 * @link https://github.com/Upstatement/routes
 * @var array
 */

return [];
