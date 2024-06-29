<?php

/**
 * WordPress Actions Configuration
 *
 * This configuration file contains an array representing the mapping of WordPress actions to callback functions.
 * Each array key is the name of a WordPress action hook, and the corresponding value is the function (or method)
 * that should be executed when the action is triggered.
 *
 * The callback functions or methods can be specified either as a string (for global functions)
 * or as an array (typically for static class methods or instance methods).
 *
 * Example:
 * return [
 *     'init' => 'my_init_function',                             // Global function
 *     'wp_head' => ['MyClass', 'my_wp_head_static_method'],     // Static class method
 *     'template_redirect' => [$myObject, 'redirect_method'],    // Instance method
 *     ...
 * ];
 *
 * Note: Ensure that the specified functions or methods are defined and accessible at the point
 * where the action is triggered.
 *
 * @var array
 */
return [];
