<?php

/**
 * Includes
 *
 * Array of files (can be filename minus .php extension) to include in the theme from the '/inc' directory
 * of the theme. Checks first for files in child theme then parent (PressGang).
 *
 * This folder is used for adding generic(or child theme specific) PHP extension to WordPress
 * - de-clutter functions.php!
 *
 * Note:- fails silently if file not found!
 *
 * @var array
 *
 */
return [
	'disable-emojis',
	'opengraph',
	'editor-styles',
];
