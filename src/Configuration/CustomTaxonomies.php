<?php

namespace PressGang\Configuration;

use PressGang\Controllers\Traits\HasCustomLabels;

/**
 * Registers custom taxonomies from config/custom-taxonomies.php on the init hook.
 * Labels are auto-generated from the config key via the HasCustomLabels trait.
 *
 * Why: keeps taxonomy registration declarative and consistent across parent/child themes.
 * Extend via: child theme config override or pressgang_taxonomy_{key}_args filter.
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
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'register_custom_taxonomies' ], 5 );
	}

	/**
	 * Registers custom taxonomies based on the provided configuration.
	 *
	 * Iterates through the configuration array and registers each custom taxonomy with WordPress.
	 * Custom labels for taxonomies are handled by the HasCustomLabels trait.
	 */
	public function register_custom_taxonomies(): void {
		foreach ( $this->config as $key => $args ) {

			$object_type = isset( $args['object-type'] ) ? $args['object-type'] : 'post';
			$args        = $this->parse_labels( $key, $args['args'] );

			$key  = \apply_filters( "pressgang_taxonomy_{$key}", $key );
			$args = \apply_filters( "pressgang_taxonomy_{$key}_args", $args );

			\register_taxonomy( $key, $object_type, $args );
		}
	}
}
