<?php

namespace PressGang\Configuration;

/**
 * Class RemoveNodes
 *
 * Manages the removal of nodes (items) from the WordPress admin toolbar.
 * This class uses a configuration array to define the IDs of the toolbar nodes that should be removed.
 * It extends ConfigurationSingleton to ensure that it is only instantiated once.
 *
 * @package PressGang
 */
class RemoveNodes extends ConfigurationSingleton {

	/**
	 * Initializes the RemoveNodes class with configuration data.
	 *
	 * Sets up the configuration for toolbar nodes removal and adds an action hook
	 * to remove nodes from the admin toolbar.
	 *
	 * @param array $config The configuration array for nodes to be removed.
	 */
	public function initialize( $config ) {
		$this->config = $config;
		add_action( 'admin_bar_menu', [ $this, 'remove_toolbar_node' ], 999 );
	}

	/**
	 * Removes specified nodes from the WordPress admin toolbar.
	 *
	 * Iterates through the configuration array and removes each specified node from the toolbar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WordPress Admin Bar object.
	 */
	public function remove_toolbar_node( $wp_admin_bar ) {
		foreach ( $this->config as $node ) {
			$wp_admin_bar->remove_node( $node );
		}
	}
}
