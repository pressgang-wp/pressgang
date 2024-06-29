<?php

/**
 * Widget Includes Configuration
 *
 * Defines an array of filenames (without the .php extension) for widget files to include in the theme.
 * These files should be placed in the '/widgets' directory of the theme.
 *
 * The Loader class checks for these files first in the child theme and then in the parent theme (PressGang).
 * This directory is specifically reserved for WordPress widgets included in the theme.
 *
 * Widgets can optionally extend the PressGang base Widget class for a consistent structure and functionality.
 * See: https://github.com/pressgang-wp/pressgang/blob/master/classes/widget.php
 *
 * Note: The inclusion process fails silently if a file is not found.
 * Ensure that the specified files exist in the '/widgets' directory of either the child or parent theme.
 *
 * Usage:
 * return [
 *     'example-widget', // Filename is 'example-widget.php'
 *     'another-widget', // Filename is 'another-widget.php'
 *     ... additional widget files ...
 * ];
 *
 * @see https://codex.wordpress.org/Widgets_API
 * @var array
 */

return [];
