<?php

/**
 * Support Configuration
 *
 * Configures theme support options using WordPress's add_theme_support() function.
 * This file defines an array of theme support features to be enabled in the theme.
 *
 * Usage:
 * - For simple theme support features, use a string entry (e.g., 'post-thumbnails').
 * - For features that require additional arguments, use an associative array (e.g., 'html5' => [...]).
 *
 * @see https://developer.wordpress.org/reference/functions/add_theme_support/
 *
 * Example configuration:
 * return [
 *     'post-thumbnails',
 *     'html5' => [
 *         'comment-list',
 *         'comment-form',
 *         'search-form',
 *         'gallery',
 *         'caption',
 *     ],
 *     ... additional theme support features ...
 * ];
 *
 * Core values include:
 *
 * 'admin-bar'
 * 'align-wide'
 * 'automatic-feed-links'
 * 'core-block-patterns'
 * 'custom-background'
 * 'custom-header'
 * 'custom-line-height'
 * 'custom-logo'
 * 'customize-selective-refresh-widgets'
 * 'custom-spacing'
 * 'custom-units'
 * 'dark-editor-style'
 * 'disable-custom-colors'
 * 'disable-custom-font-sizes'
 * 'editor-color-palette'
 * 'editor-gradient-presets'
 * 'editor-font-sizes'
 * 'editor-styles'
 * 'featured-content'
 * 'html5'
 * 'menus'
 * 'post-formats'
 * 'post-thumbnails'
 * 'responsive-embeds'
 * 'starter-content'
 * 'title-tag'
 * 'wp-block-styles'
 * 'widgets'
 * 'widgets-block-editor'
 *
 * @var array
 */
return [
	'html5' => [
		'comment-list',
		'comment-form',
		'search-form',
		'gallery',
		'caption',
	],
	'post-thumbnails',
];
