<?php

namespace PressGang\Controllers\WooCommerce;

/**
 * Resolves the appropriate WooCommerce controller based on the current query.
 */
class WooCommerceControllerResolver {

	/**
	 * Determines the controller class for the current WooCommerce page.
	 *
	 * @return string The fully qualified controller class name.
	 */
	public static function resolve(): string {
		if ( \is_singular( 'product' ) ) {
			return ProductController::class;
		}

		if ( \is_product_category() ) {
			return match ( \get_option( 'woocommerce_category_archive_display' ) ) {
				'subcategories' => ProductCategoryController::class,
				'both' => ProductCategoriesAndProductsController::class,
				default => ProductsController::class,
			};
		}

		if ( \is_shop() ) {
			return match ( \get_option( 'woocommerce_shop_page_display' ) ) {
				'subcategories' => ProductCategoriesController::class,
				'both' => ProductCategoriesAndProductsController::class,
				default => ProductsController::class,
			};
		}

		return ProductsController::class;
	}
}
