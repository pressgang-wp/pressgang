<?php

namespace PressGang\Configuration;

/**
 * Class Sidebars
 *
 * Manages the registration of sidebars (widget areas) in the WordPress theme.
 * Adds sidebar configurations to the theme and integrates them with Timber for rendering in Twig templates.
 *
 * @see https://wordpress.org/documentation/article/manage-wordpress-widgets/
 * @package PressGang
 */
class Sidebars extends ConfigurationSingleton {

	/**
	 * Initializes the Sidebars class with configuration data.
	 *
	 * Sets up theme support for widgets, registers sidebars based on the provided configuration,
	 * and adds Timber functions for sidebar rendering.
	 *
	 * @param array $config The configuration array for sidebars.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_theme_support( 'widgets' );
		\add_action( 'widgets_init', [ $this, 'register_sidebars' ] );
	}

	/**
	 * Registers sidebars in the WordPress theme.
	 *
	 * Iterates through the sidebar configuration array and registers each sidebar.
	 * Allows for filtering of sidebar parameters using a specific hook.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_sidebars/
	 */
	public function register_sidebars(): void {
		foreach ( $this->config as $key => $args ) {
			$args = \apply_filters( "pressgang_widget_{$key}", $args );
			if ( is_array( $args ) ) {
				\register_sidebar( $this->parse_args( $args ) );
			}
		}
	}

	/**
	 * Parses and merges sidebar arguments with default values.
	 *
	 * @param array $args Sidebar configuration arguments.
	 *
	 * @return array Merged array of arguments.
	 */
	public function parse_args( array $args ): array {
		$defaults = [
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '',
			'after_title'   => '',
		];

		return \wp_parse_args( $args, $defaults );
	}

}
