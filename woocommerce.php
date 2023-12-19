<?php

require_once( __DIR__ . '/controllers/woocommerce-product-controller.php' );
require_once( __DIR__ . '/controllers/woocommerce-products-controller.php' );
require_once( __DIR__ . '/controllers/woocommerce-product-category-controller.php' );

$controller = null;

if ( is_singular( 'product' ) ) {
	$controller = new \PressGang\WooCommerceProductController();
} else if ( is_product_category() ) {
	$controller = new \PressGang\WooCommerceProductCategoryController();
} else {
	$controller = new \PressGang\WooCommerceProductsController();
}

$controller->render();
