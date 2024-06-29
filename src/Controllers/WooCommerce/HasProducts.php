<?php

namespace PressGang\Controllers\WooCommerce;

/**
 * Trait for controllers with products functionality.
 *
 * Provides a method to retrieve and cache an array of WooCommerce product objects.
 */
trait HasProducts {

	protected array $products = [];

	/**
	 * Get an array of WooCommerce product objects.
	 *
	 * Retrieves and caches an array of product objects based on the current WordPress query.
	 * This method iterates over each post obtained from get_posts() and converts them to WooCommerce product objects.
	 *
	 * @see https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-product-functions.html#function_wc_get_product
	 * @return array An array of WooCommerce product objects.
	 */
	protected function get_products(): array {
		if ( empty( $this->products ) ) {
			foreach ( $this->get_posts() as $post ) {
				$this->products[] = \wc_get_product( $post->id );
			}
		}

		return $this->products;
	}
}
