<?php

if ( ! defined( 'THEMENAME' ) ) {
	define( 'THEMENAME', 'pressgang' );
}

$autoload_path = \get_stylesheet_directory() . '/vendor/autoload.php';
if ( file_exists( $autoload_path ) ) {
	require_once $autoload_path;
}

// Initialize the PressGang theme
( new PressGang\PressGang() )->boot();
