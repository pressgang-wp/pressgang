<?php

if ( ! defined( 'THEMENAME' ) ) {
	define( 'THEMENAME', 'pressgang' );
}

// Ensure composer is auto loaded
require_once( __DIR__ . '/vendor/autoload.php' );

/**
 * Go!
 *
 */
require_once( 'core/site.php' );
