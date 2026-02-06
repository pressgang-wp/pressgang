<?php

namespace PressGang\Configuration;

use PressGang\Controllers\Traits\HasCustomLabels;

/**
 * Registers custom post types from config/custom-post-types.php on the init hook.
 * Labels are auto-generated from the config key via the HasCustomLabels trait.
 *
 * Why: keeps CPT registration declarative and consistent across parent/child themes.
 * Extend via: child theme config override or pressgang_cpt_{key}_args filter.
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
	#[\Override]
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'register_custom_post_types' ] );
	}

	/**
	 * Registers custom post types based on the provided configuration.
	 *
	 * Iterates through the configuration array and registers each custom post type with WordPress.
	 * Custom labels for post types are handled by the HasCustomLabels trait.
	 */
	public function register_custom_post_types(): void {
		foreach ( $this->config as $key => $args ) {

			$args = $this->parse_labels( $key, $args );

			$key  = \apply_filters( "pressgang_cpt_{$key}", $key );
			$args = \apply_filters( "pressgang_cpt_{$key}_args", $args );
			\register_post_type( $key, $args );
		}

	}
}
