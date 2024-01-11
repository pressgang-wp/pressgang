<?php

namespace PressGang\Configuration;

use PressGang\Traits\HasCustomLabels;

/**
 * Class CustomPostTypes
 *
 * Handles the registration of custom post types in WordPress.
 * This class uses a configuration array to define the settings for each custom post type.
 * It extends ConfigurationSingleton to ensure that it is only instantiated once.
 *
 * @package PressGang
 */
class CustomPostTypes extends ConfigurationSingleton {

	use HasCustomLabels;

	/**
	 * Initializes the CustomPostTypes class with configuration data.
	 *
	 * Sets up the configuration for custom post types and adds an action hook
	 * to register the custom post types after WordPress initializes.
	 *
	 * @param array $config The configuration array for custom post types.
	 */
	public function initialize( array $config ) {
		$this->config = $config;
		add_action( 'init', [ $this, 'register_custom_post_types' ] );
	}

	/**
	 * Registers custom post types based on the provided configuration.
	 *
	 * Iterates through the configuration array and registers each custom post type with WordPress.
	 * Custom labels for post types are handled by the HasCustomLabels trait.
	 */
	public function register_custom_post_types() {
		foreach ( $this->config as $key => $args ) {

			$args = $this->parse_labels( $key, $args );

			$key  = apply_filters( "pressgang_cpt_{$key}", $key );
			$args = apply_filters( "pressgang_cpt_{$key}_args", $args );
			register_post_type( $key, $args );
		}

	}
}
