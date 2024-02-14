<?php

namespace PressGang\Controllers\WooCommerce;

use Timber\Timber;

/**
 * Trait for controllers with products functionality.
 *
 * Provides a method to retrieve and cache an array of WooCommerce product categories with image.
 */
trait HasProductCategories {

	protected $product_categories = [];

	/**
	 * Get the product categories.
	 *
	 * Retrieves and caches the product categories for the current term. Each category is enhanced with additional
	 * data like thumbnail images. This method uses Timber to convert categories into term objects.
	 *
	 * @return array An array of Timber term objects representing product categories.
	 */
	public function get_product_categories(): array {

		if ( empty( $this->product_categories ) ) {

			$term      = \get_queried_object();
			$parent_id = empty( $term->term_id ) ? 0 : $term->term_id;

			$args = \apply_filters( 'woocommerce_product_subcategories_args', [
				'parent'       => $parent_id,
				'menu_order'   => 'ASC',
				'hide_empty'   => true,
				'hierarchical' => 1,
				'taxonomy'     => 'product_cat',
				'pad_counts'   => 1,
			] );

			$product_categories = Timber::get_terms( $args );

			foreach ( $product_categories as &$category ) {
				if ( $thumbnail_id = $category->meta( 'thumbnail_id' ) ) {
					$category->thumbnail = Timber::get_image( $thumbnail_id );
				}
			}

			$this->product_categories = $product_categories;
		}

		return $this->product_categories;
	}
}
