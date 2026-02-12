<?php

namespace PressGang\Controllers\WooCommerce;

use Timber\Timber;

/**
 * Trait for controllers with a shop_sidebar
 *
 */
trait HasShopSidebar {

	protected ?string $sidebar = null;

	/**
	 * Retrieve the sidebar content.
	 *
	 * Fetches and caches the sidebar content for the shop sidebar. This method is marked for deprecation.
	 *
	 * @return string The sidebar content.
	 */
	protected function get_sidebar(): string {
		if ( $this->sidebar === null ) {
			$this->sidebar = Timber::get_widgets( 'shop_sidebar' );
		}

		return $this->sidebar;
	}
}
