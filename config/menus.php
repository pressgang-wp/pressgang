<?php

/**
 * Menus Configuration
 *
 * This configuration file defines settings for navigation menus used within the WordPress theme.
 * Each entry in the array represents a navigation menu location, with a description that will appear
 * in the WordPress admin area.
 *
 * The configuration array is a proxy to the `register_nav_menus` function in WordPress,
 * where each array key is the menu location and the corresponding value is the description of the menu.
 *
 * See: https://developer.wordpress.org/reference/functions/register_nav_menus/
 *
 * Example configuration array:
 * return [
 *     'primary' => 'Primary Navigation',
 *     'footer' => 'Footer Navigation',
 *     ... additional menus ...
 * ];
 *
 * Note: The keys ('primary', 'footer', etc.) are used as menu locations, which can be used in the theme to display menus.
 *
 * @var array
 */
return [
	'primary' => 'Primary Navigation',
];
