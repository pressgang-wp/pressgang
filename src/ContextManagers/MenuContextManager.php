<?php

namespace PressGang\ContextManagers;

use Timber\Timber;

/**
 * Adds registered WordPress navigation menus to the Timber context as menu_{location}.
 * Each menu is filterable via the pressgang_context_menu_{location} hook.
 */
class MenuContextManager implements ContextManagerInterface {

	/**
	 * @param array<string, mixed> $context
	 *
	 * @return array<string, mixed>
	 */
	#[\Override]
	public function add_to_context( array $context ): array {
		$registered_menus = \get_registered_nav_menus();

		foreach ( $registered_menus as $location => $description ) {
			if ( \has_nav_menu( $location ) ) {
				$menu = \apply_filters( "pressgang_context_menu_{$location}", $this->get_menu( $location ) );
				$context["menu_{$location}"] = $menu;
			}
		}

		return $context;
	}

	/**
	 * Retrieves a Timber menu for the given location.
	 *
	 * @param string $location
	 *
	 * @return \Timber\Menu|null
	 */
	protected function get_menu( string $location ): ?object {
		return Timber::get_menu( $location );
	}
}
