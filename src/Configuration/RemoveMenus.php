<?php

namespace PressGang\Configuration;

/**
 * Class RemoveMenus
 *
 * Manages the removal of menu items from the WordPress admin panel based on provided configuration.
 *
 * @package PressGang
 */
class RemoveMenus extends ConfigurationSingleton {

	/**
	 * @var array Configurations for menu removal.
	 */
	protected array $config;

	/**
	 * Initializes the RemoveMenus object with configuration and hooks into the WordPress 'admin_menu' action.
	 *
	 * @param array $config An array of admin menu slugs to be removed.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'admin_menu', [ $this, 'remove_menus' ], 999 );
	}

	/**
	 * Removes configured menu pages from the WordPress admin area.
	 *
	 * This method iterates through the $config array, removing each specified menu page using the remove_menu_page function.
	 *
	 * @hooked admin_menu
	 */
	public function remove_menus(): void {
		foreach ( $this->config as $menu ) {
			\remove_menu_page( $menu );
		}
	}
}
