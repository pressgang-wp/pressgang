<?php

namespace PressGang\Configuration;

use PressGang\Helpers\StyleLoader;

/**
 * Class Styles
 *
 * Manages the registration, enqueueing, and additional attributes of CSS stylesheets in the WordPress theme.
 *
 * @package PressGang\Configuration
 */
class Styles extends ConfigurationSingleton {

	/**
	 * Initializes the Styles class with configuration data.
	 *
	 * Registers styles based on provided configuration and adds necessary hooks to WordPress.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
	 *
	 * @param array $config Configuration array for styles.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'handle_styles' ] );
		\add_filter( 'style_loader_tag', [ StyleLoader::class, 'add_style_attrs' ], 10, 4 );
	}

	/**
	 * Registers stylesheets in WordPress.
	 *
	 * Iterates through the configuration array and registers each stylesheet,
	 * also enqueues them if specified.
	 *
	 * @see https://codex.wordpress.org/Function_Reference/wp_register_style
	 */
	public function handle_styles(): void {
		foreach ( $this->config as $handle => $args ) {
			StyleLoader::register_style( $handle, $args );
		}
	}
}
