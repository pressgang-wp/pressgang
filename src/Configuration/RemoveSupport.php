<?php

namespace PressGang\Configuration;

/**
 * Class Remove Support
 *
 * Handles the removal of default features in a WordPress theme.
 *
 * @see https://developer.wordpress.org/reference/functions/remove_theme_support/
 * @package PressGang
 */
class RemoveSupport extends ConfigurationSingleton {

	/**
	 * Removes support features.
	 *
	 * Iterates through each configuration item and adds theme support using WordPress's remove_theme_support function.
	 *
	 * @param array $config Configuration array for theme support options to remove.
	 */
	public function initialize( array $config ): void {
		foreach ( $config as $key ) {
			\remove_theme_support( $key );
		}
	}
}
