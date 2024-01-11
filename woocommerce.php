<?php

require_once( __DIR__ . '/controllers/woocommerce-product-controller.php' );
require_once( __DIR__ . '/controllers/woocommerce-products-controller.php' );
require_once( __DIR__ . '/controllers/woocommerce-product-category-controller.php' );

$controller = null;

if ( is_singular( 'product' ) ) {
	$controller = new \PressGang\Controllers\WooCommerce\ProductController();
} else if ( is_product_category() ) {
	$controller = new \PressGang\Controllers\WooCommerce\ProductCategoryController();
} else {
	$controller = new \PressGang\Controllers\WooCommerce\ProductsController();
}

$controller->render();
