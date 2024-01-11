<?php

namespace PressGang\Configuration;

/**
 * Class Support
 *
 * Handles the addition of theme support features in a WordPress theme.
 * This class allows for configuring various WordPress theme support options,
 * such as post thumbnails, HTML5 support, custom logo, etc.
 *
 * @package PressGang
 */
class Support {

	/**
	 * Initializes the theme support features based on provided configuration.
	 *
	 * Iterates through each configuration item and adds theme support using WordPress's add_theme_support function.
	 * The configuration can specify support options either as a simple string (for features without additional arguments)
	 * or as an associative array for features that require arguments.
	 *
	 * @param array $config Configuration array for theme support options.
	 */
	public function initialize( $config ) {
		foreach ( $config as $key => $args ) {
			if ( is_numeric( $key ) ) {
				// Simple theme support feature, added directly
				\add_theme_support( $args );
			} elseif ( is_array( $args ) ) {
				// Theme support feature that requires additional arguments
				\add_theme_support( $key, $args );
			}
		}
	}
}
