<?php

/**
 * PHPUnit bootstrap for PressGang unit tests.
 *
 * Loads the Composer autoloader and defines constants that the source code
 * expects to exist at runtime (normally set by WordPress / functions.php).
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! defined( 'THEMENAME' ) ) {
	define( 'THEMENAME', 'pressgang' );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}
