<?php

namespace PressGang\Configuration;

use Timber\Timber;

/**
 * Registers custom block patterns from config/block-patterns.php. If a pattern entry
 * omits 'content', it is automatically compiled from a matching Twig template in the
 * block-patterns/ views directory.
 *
 * Why: allows patterns to be defined declaratively, with optional Twig-based content.
 * Extend via: child theme config override.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-patterns/
 */
class BlockPatterns extends ConfigurationSingleton {

	/**
	 * Initializes the BlockPatterns class with configuration data.
	 *
	 * Sets up the configuration and adds an action hook to register block patterns.
	 *
	 * @param array $config The configuration array for block patterns.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'register_block_patterns' ] );
	}

	/**
	 * Registers all block patterns defined in the configuration.
	 *
	 * Iterates through the configuration array and registers each block pattern.
	 * If the 'content' field is not set, it tries to load it from a Twig template named
	 * after the pattern key in the 'block-patterns' directory.
	 */
	public function register_block_patterns(): void {
		foreach ( $this->config as $key => $args ) {
			if ( empty( $args['content'] ) ) {
				$args['content'] = Timber::compile( "block-patterns/{$key}.twig" );
			}

			\register_block_pattern( $key, $args );
		}
	}
}
