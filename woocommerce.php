<?php

use PressGang\Controllers\WooCommerce\ProductCategoryController;
use PressGang\Controllers\WooCommerce\ProductController;
use PressGang\Controllers\WooCommerce\ProductsController;

$controller = ProductsController::class;

if ( \is_singular( 'product' ) ) {
	$controller = ProductController::class;
} else if ( \is_product_category() ) {
	$controller = ProductCategoryController::class;
}

PressGang\PressGang::render( controller: $controller );
