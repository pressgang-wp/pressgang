<?php

/**
 * Timber configuration.
 *
 * Child themes can override this file to enable/disable Twig compilation cache.
 *
 * Note: config files are merged by filename, so child theme `config/timber.php`
 * replaces this file entirely.
 */
return [
	'twig' => [
		// Keep disabled by default at the framework level.
		'cache_enabled' => false,

		// Absolute path recommended when cache is enabled.
		'cache_path' => WP_CONTENT_DIR . '/cache/twig',

		// Safe defaults; child themes can override per environment.
		'auto_reload' => WP_DEBUG,
		'debug'       => WP_DEBUG,
	],
];
