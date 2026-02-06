<?php

namespace PressGang\Configuration;

/**
 * Registers navigation menu locations from config/menus.php (location => description
 * pairs). Each location is filterable via pressgang_register_menu_{location} before
 * being passed to register_nav_menus().
 *
 * Why: keeps menu location registration declarative and filterable.
 * Extend via: child theme config override or pressgang_register_menu_{location} filter.
 *
 * @see https://developer.wordpress.org/reference/functions/register_nav_menus/
 */
class Menus extends ConfigurationSingleton {

	/**
	 * @param array<string, string> $config Location => description pairs.
	 */
	public function initialize( array $config ): void {
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
			$menu = \apply_filters( "pressgang_register_menu_{$location}", [ $location => $description ] );

			if ( ! empty( $menu ) ) {
				$menus = array_merge( $menus, $menu );
			}
		}

		\register_nav_menus( $menus );
	}
}
