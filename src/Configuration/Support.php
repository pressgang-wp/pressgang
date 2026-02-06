<?php

namespace PressGang\Configuration;

/**
 * Calls add_theme_support() for each feature listed in config/support.php.
 * Supports both simple string entries and associative arrays with additional arguments.
 *
 * Why: keeps theme support declarations centralised and overridable.
 * Extend via: child theme config override.
 *
 * @see https://developer.wordpress.org/reference/functions/add_theme_support/
 */
class Support extends ConfigurationSingleton {

	/**
	 * Initializes the theme support features based on provided configuration.
	 *
	 * Iterates through each configuration item and adds theme support using WordPress's add_theme_support function.
	 * The configuration can specify support options either as a simple string (for features without additional arguments)
	 * or as an associative array for features that require arguments.
	 *
	 * @param array $config Configuration array for theme support options.
	 */
	#[\Override]
	public function initialize( array $config ): void {
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
