<?php

use PressGang\Controllers\WooCommerce\{
	ProductCategoriesAndProductsController,
	ProductCategoriesController,
	ProductCategoryController,
	ProductsController,
	ProductController,
};

// Default to ProductsController or handle 'both' case
$controller = ProductsController::class;

// Determine the controller based on the current page and shop display settings
if ( \is_singular( 'product' ) ) {
	$controller = ProductController::class;
} elseif ( \is_product_category() ) {
	switch (\get_option( 'woocommerce_category_archive_display' ) ) {
		case 'subcategories':
			$controller = ProductCategoryController::class;
			break;
		case 'both':
			$controller = ProductCategoriesAndProductsController::class;
			break;
	}
} elseif ( is_shop() ) {
	switch (\get_option( 'woocommerce_shop_page_display' )) {
		case 'subcategories':
			$controller = ProductCategoriesController::class;
			break;
		case 'both':
			$controller = ProductCategoriesAndProductsController::class;
			break;
	}
}

PressGang\PressGang::render( controller: $controller );
