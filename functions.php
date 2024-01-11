<?php

if ( ! defined( 'THEMENAME' ) ) {
	define( 'THEMENAME', 'pressgang' );
}

require __DIR__ . '/vendor/autoload.php';

// Initialize the PressGang theme
(new PressGang\PressGang())->boot();
