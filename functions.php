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

// PressGang is a parent-theme framework: it boots only when a child theme supplies
// the Composer autoloader above. If the framework class is unavailable — because
// PressGang was activated or "Live Preview"-ed directly — degrade to an explanatory
// message instead of a fatal "class not found" (here and in every template stub).
if ( ! class_exists( PressGang\PressGang::class ) ) {
	require_once __DIR__ . '/bootstrap-fallback.php';
	return;
}

// Initialize the PressGang theme.
( new PressGang\PressGang( new Loader( new FileConfigLoader() ) ) )->boot();
