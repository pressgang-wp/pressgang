<?php

use PressGang\Controllers\WooCommerce\{
	ProductCategoriesController,
	ProductCategoryController,
	ProductController,
	ProductsController
};
use PressGang\PressGang;

$is_singular_product = \is_singular('product');
$is_product_category = \is_product_category();
$shop_display = \get_option('woocommerce_shop_page_display');

// Determine the controller based on the current page and shop display settings
if ($is_singular_product) {
	$controller = ProductController::class;
} elseif ($is_product_category) {
	$controller = ProductCategoryController::class;
} elseif ($shop_display === 'subcategories') {
	$controller = ProductCategoriesController::class;
} else {
	// Default to ProductsController or handle 'both' case here
	$controller = ProductsController::class;
}

PressGang::render(controller: $controller);
