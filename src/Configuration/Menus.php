<?php

namespace PressGang\Configuration;

/**
 * Class Menus
 *
 * Manages the registration of navigation menus in WordPress.
 * This class uses a configuration array to define the settings for each menu.
 * It extends ConfigurationSingleton to ensure that it is only instantiated once.
 *
 * @see https://developer.wordpress.org/reference/functions/register_nav_menus/
 * @package PressGang
 */
class Menus extends ConfigurationSingleton {

	/**
	 * __construct
	 *
	 * Register Menus
	 *
	 */
	public function initialize( $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'register_nav_menus' ] );
	}

	/**
	 * Registers navigation menus based on the provided configuration.
	 *
	 * Iterates through the configuration array and registers each menu with WordPress.
	 * Menus can be filtered in child themes and plugins if required.
	 */
	public function register_nav_menus(): void {

		$menus = [];

		foreach ( $this->config as $location => $description ) {
			// allow filtering of the menu in child themes and plugins if required
			$menu = \apply_filters( "pressgang_menu_{$location}", [ $location => $description, ] );

			if ( ! empty( $menu ) ) {
				$menus[ $location ] = $description;
			}
		}

		\register_nav_menus( $menus );
	}
}
