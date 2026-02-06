<?php

namespace PressGang\Configuration;

use PressGang\Helpers\StyleLoader;

/**
 * Registers and enqueues CSS stylesheets from config/styles.php. Delegates to
 * StyleLoader for versioning, enqueueing, and optional preconnect link injection.
 *
 * Why: keeps stylesheet registration declarative with automatic cache-busting.
 * Extend via: child theme config override.
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
	#[\Override]
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
