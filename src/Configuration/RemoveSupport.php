<?php

namespace PressGang\Configuration;

/**
 * Calls remove_theme_support() for each feature listed in config/remove-support.php.
 *
 * Why: allows child themes to declaratively disable parent theme features.
 * Extend via: child theme config override.
 *
 * @see https://developer.wordpress.org/reference/functions/remove_theme_support/
 */
class RemoveSupport extends ConfigurationSingleton {

	/**
	 * Removes support features.
	 *
	 * Iterates through each configuration item and adds theme support using WordPress's remove_theme_support function.
	 *
	 * @param array $config Configuration array for theme support options to remove.
	 */
	#[\Override]
	public function initialize( array $config ): void {
		foreach ( $config as $key ) {
			\remove_theme_support( $key );
		}
	}
}
