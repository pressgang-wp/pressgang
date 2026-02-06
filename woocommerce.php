<?php

/**
 * The template for displaying WooCommerce content.
 *
 * @package PressGang
 */

use PressGang\Controllers\WooCommerce\WooCommerceControllerResolver;

PressGang\PressGang::render( controller: WooCommerceControllerResolver::resolve() );
