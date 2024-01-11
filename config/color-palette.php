<?php

/**
 * Color Palette Configuration
 *
 * This configuration file defines a custom color palette for the WordPress Block Editor.
 * Each entry in the array represents a color in the palette.
 *
 * The configuration for each color can be specified in two ways:
 * 1. As an associative array with keys:
 *    - 'name': A human-readable name for the color, which will appear in the Block Editor.
 *    - 'color': The hexadecimal color code.
 * 2. As a simple key-value pair, where the key is the slug (kebab-case) and the value is the color code.
 *    In this case, the 'name' will be auto-generated from the slug.
 *
 * Example configuration array:
 * return [
 *     'strong-blue' => '#0073aa', // Simple key-value pair
 *     'light-gray' => [          // Detailed associative array
 *         'name' => 'Light Gray',
 *         'color' => '#eee',
 *     ],
 *     ... additional colors ...
 * ];
 *
 * Note: If the array is left empty, no custom color palette will be registered.
 *
 * @var array
 */
return [
	// Define colors for the Block Editor color palette here
];
