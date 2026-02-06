<?php

namespace PressGang\Configuration;

/**
 * Removes nodes from the WordPress admin toolbar by ID, based on
 * config/remove-nodes.php. Each entry is a node ID passed to
 * WP_Admin_Bar::remove_node() on the admin_bar_menu hook.
 *
 * Why: lets themes simplify the admin bar declaratively.
 * Extend via: child theme config override.
 */
class RemoveNodes extends ConfigurationSingleton {

	/**
	 * Initializes the RemoveNodes class with configuration data.
	 *
	 * Sets up the configuration for toolbar nodes removal and adds an action hook
	 * to remove nodes from the admin toolbar.
	 *
	 * @par\am array $config The configuration array for nodes to be removed.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'admin_bar_menu', [ $this, 'remove_toolbar_node' ], 999 );
	}

	/**
	 * Removes specified nodes from the WordPress admin toolbar.
	 *
	 * Iterates through the configuration array and removes each specified node from the toolbar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The WordPress Admin Bar object.
	 */
	public function remove_toolbar_node( \WP_Admin_Bar $wp_admin_bar ): void {
		foreach ( $this->config as $node ) {
			$wp_admin_bar->remove_node( $node );
		}
	}
}
