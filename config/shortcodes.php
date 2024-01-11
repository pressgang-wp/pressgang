<?php

/**
 * Shortcode Includes Configuration
 *
 * Defines an array of filenames (without the .php extension) for shortcode files to include in the theme.
 * These files are located in the '/shortcodes' directory of the theme.
 *
 * The Loader class checks for these files first in the child theme and then in the parent theme (PressGang).
 * This directory is specifically reserved for WordPress shortcodes included in the theme.
 *
 * Shortcodes can optionally extend the PressGang base Shortcode class for consistent structure and functionality.
 * See: https://github.com/pressgang-wp/pressgang/blob/master/classes/shortcode.php
 *
 * Note: The inclusion process fails silently if a file is not found.
 * Ensure that the specified files exist in the '/shortcodes' directory of either the child or parent theme.
 *
 * Usage:
 * return [
 *     'example-shortcode', // Filename is 'example-shortcode.php'
 *     'another-shortcode', // Filename is 'another-shortcode.php'
 *     ... additional shortcode files ...
 * ];
 *
 * @see https://codex.wordpress.org/Shortcode_API
 * @var array
 */

return [];
