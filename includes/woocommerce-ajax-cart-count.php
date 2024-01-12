<?php

class WooCommerceAjaxCartCount {

	/**
	 * WooCommerceAjaxCartCount constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cart_count_fragment' ] );
	}

	/**
	 * cart_count_retriever
	 *
	 */
	public function cart_count_fragment( $fragments ) {
		global $woocommerce;

		$fragments['a#cart-link'] = \Timber\Timber::compile( 'woocommerce/cart-link.twig', [
			'cart_link'           => esc_url( wc_get_cart_url() ),
			'cart_contents_count' => $woocommerce->cart->cart_contents_count
		] );

		return $fragments;
	}
}

new WooCommerceAjaxCartCount();
