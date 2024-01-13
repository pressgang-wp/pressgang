<?php

namespace PressGang\ContextManagers;

use Timber\Timber;

/**
 * Class MenuContextManager
 *
 * Manages the addition of WordPress navigation menus to the Timber context. This class
 * ensures that registered menus are available in the Timber context for use in templates.
 * Implements the ContextManagerInterface to ensure consistency with the PressGang context
 * management system.
 *
 * @package PressGang\ContextManagers
 */
class MenuContextManager implements ContextManagerInterface {

	/**
	 * Adds registered WordPress navigation menus to the Timber context.
	 *
	 * This method retrieves all registered navigation menus using the WordPress
	 * function get_registered_nav_menus. It then checks if each menu location has
	 * an assigned menu and, if so, adds it to the Timber context. This makes it
	 * possible to access and render these menus in the theme's templates using Timber.
	 *
	 * @param array $context The Timber context array that is passed to templates.
	 *
	 * @return array The modified context array with navigation menus added.
	 */
	public function add_to_context( array $context ): array {
		$registered_menus = \get_registered_nav_menus();

		foreach ( $registered_menus as $location => $description ) {
			if ( \has_nav_menu( $location ) ) {
				$context["menu_{$location}"] = Timber::get_menu( $location );
			}
		}

		return $context;
	}

}
