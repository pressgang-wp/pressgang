<?php

/**
 * PHPStan bootstrap.
 *
 * Defines constants that source code expects to exist at runtime (normally
 * set by WordPress / functions.php), mirroring tests/bootstrap.php.
 */

if ( ! defined( 'THEMENAME' ) ) {
	define( 'THEMENAME', 'pressgang' );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
