<?php

namespace PressGang\Configuration;

use PressGang\Traits\HasCustomLabels;

/**
 * Class CustomTaxonomies
 *
 * Manages the registration of custom taxonomies in WordPress.
 * This class uses a configuration array to define the settings for each custom taxonomy.
 * It extends ConfigurationSingleton to ensure that it is only instantiated once and utilizes
 * the HasCustomLabels trait for generating labels for taxonomies.
 *
 * @package PressGang
 */
class CustomTaxonomies extends ConfigurationSingleton {

	use HasCustomLabels;

	/**
	 * Initializes the CustomTaxonomies class with configuration data.
	 *
	 * Sets up the configuration for custom taxonomies and adds an action hook
	 * to register the custom taxonomies after WordPress initializes.
	 *
	 * @param array $config The configuration array for custom taxonomies.
	 */
	public function initialize( $config ) {
		$this->config = $config;
		\add_action( 'init', [ $this, 'register_custom_taxonomies' ], 5 );
	}

	/**
	 * Registers custom taxonomies based on the provided configuration.
	 *
	 * Iterates through the configuration array and registers each custom taxonomy with WordPress.
	 * Custom labels for taxonomies are handled by the HasCustomLabels trait.
	 */
	public function register_custom_taxonomies() {
		foreach ( $this->config as $key => $args ) {

			$object_type = isset( $args['object-type'] ) ? $args['object-type'] : 'post';
			$args        = $this->parse_labels( $key, $args['args'] );

			$key  = \apply_filters( "pressgang_taxonomy_{$key}", $key );
			$args = \apply_filters( "pressgang_taxonomy_{$key}_args", $args );

			\register_taxonomy( $key, $object_type, $args );
		}
	}
}
