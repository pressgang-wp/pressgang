<?php

namespace PressGang\Configuration;

use Timber\Timber;

/**
 * Class BlockPatterns
 *
 * Manages the registration of custom block patterns for the WordPress block editor.
 * The block patterns are defined in a configuration array and registered on the 'init' hook.
 * This class extends ConfigurationSingleton to ensure that it is instantiated only once.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-patterns/
 * @package PressGang
 */
class BlockPatterns extends ConfigurationSingleton {

	/**
	 * Initializes the BlockPatterns class with configuration data.
	 *
	 * Sets up the configuration and adds an action hook to register block patterns.
	 *
	 * @param array $config The configuration array for block patterns.
	 */
	public function initialize( $config ) {
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
	public function register_block_patterns() {
		foreach ( $this->config as $key => $args ) {
			if ( empty( $args['content'] ) ) {
				$args['content'] = Timber::compile( "block-patterns/{$key}.twig" );
			}

			\register_block_pattern( $key, $args );
		}
	}
}
