<?php

/**
 * Advanced Custom Fields (ACF) Options Page Configuration
 *
 * This configuration file defines settings for ACF options pages used within the theme.
 * Each entry in the returned array represents the configuration for one ACF options page,
 * and it serves as a proxy to the `acf_add_options_page` function provided by ACF.
 *
 * The configuration array should contain associative arrays with settings for each options page.
 * These settings include parameters such as 'page_title', 'menu_title', 'menu_slug', etc.,
 * as documented in the ACF options page documentation.
 *
 * e.g.
 *
 *  'site-option' => [
 *      'page_title' => "Site Options",
 *      'menu_title' => "Site Options",
 *      'menu_slug' => 'site-options',
 *      'capability' => 'edit_posts',
 *      'redirect' => false,
 *      'position' => false,
 *      'icon_url' => false,
 *      'post_id' => 'options',
 *      'autoload' => false,
 *  ],
 *
 * @see https://www.advancedcustomfields.com/resources/acf_add_options_page/
 * @var array
 */
return [
	'site-option' => [
		'page_title' => "Site Options",
	],
];
