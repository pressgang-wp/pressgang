<?php

/**
 * Remove Support Configuration
 *
 * Configures theme support options using WordPress's remove_theme_support() function.
 * This file defines an array of core features to be removed from the theme.
 *
 * @see https://developer.wordpress.org/reference/functions/remove_theme_support/
 *
 * Example configuration:
 * return [
 *     'core-block-patterns',
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
	'title-tag',
	'post-thumbnails',
	'html5' => [
		'comment-list',
		'comment-form',
		'search-form',
		'gallery',
		'caption',
	],
];

remove_theme_support('core-block-patterns');
