<?php

namespace PressGang\Library;

use function PressGang\add_filter;
use function PressGang\esc_url;

class WooCommerceAjaxCartCount {

	/**
	 * WooCommerceAjaxCartCount constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_count_fragment' ) );
	}

	/**
	 * cart_count_retriever
	 *
	 */
	public function cart_count_fragment( $fragments ) {
		global $woocommerce;

		$fragments['a#cart-link'] = \Timber\Timber::compile( 'woocommerce/cart-link.twig', array(
			'cart_link'           => esc_url( wc_get_cart_url() ),
			'cart_contents_count' => $woocommerce->cart->cart_contents_count
		) );

		return $fragments;
	}
}

new WooCommerceAjaxCartCount();