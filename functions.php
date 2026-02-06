<?php

/**
 * Theme bootstrap entrypoint.
 *
 * Loads Composer autoloading, defines core constants, and boots the PressGang framework.
 *
 * @see https://docs.pressgang.dev/
 */

use PressGang\Bootstrap\FileConfigLoader;
use PressGang\Bootstrap\Loader;
use PressGang\ServiceProviders\TimberServiceProvider;

// Theme slug used for localisation across the framework.
// This should be overridden in child themes to ensure translations work correctly.
if ( ! defined( 'THEMENAME' ) ) {
	define( 'THEMENAME', 'pressgang' );
}

// Load Composer autoloader from the active child theme.
$autoload_path = \get_stylesheet_directory() . '/vendor/autoload.php';
if ( file_exists( $autoload_path ) ) {
	require_once $autoload_path;
}

// Initialize the PressGang theme.
( new PressGang\PressGang( new Loader( new FileConfigLoader() ), new TimberServiceProvider() ) )->boot();
