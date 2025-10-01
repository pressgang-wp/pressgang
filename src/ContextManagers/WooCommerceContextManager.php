<?php

namespace PressGang\ContextManagers;

/**
 * Class WooCommerceContextManager
 *
 * Manages the integration of key WooCommerce-related data into the Timber context.
 * This class retrieves WooCommerce specific information such as account links, cart link,
 * checkout link, and cart contents count, and makes them accessible in the theme's Twig templates.
 *
 * Implements the ContextManagerInterface to ensure consistent handling of context data within
 * the PressGang framework, specifically for WooCommerce-related features.
 *
 * @package PressGang\ContextManagers
 */
class WooCommerceContextManager implements ContextManagerInterface {

	/**
	 * Adds WooCommerce specific data to the Timber context.
	 *
	 * Retrieves various WooCommerce related links (such as account, logout, cart, and checkout)
	 * and the cart contents count, and adds them to the Timber context. This allows for easy
	 * access to these key WooCommerce features within the theme's Twig templates.
	 *
	 * @param array $context The Timber context array that is passed to templates.
	 *
	 * @return array The modified context with added WooCommerce data.
	 */
	public function add_to_context( array $context ): array {

		if ( class_exists( 'WooCommerce' ) ) {
			$account_page_id = \get_option( 'woocommerce_myaccount_page_id' );

			$context['my_account_link']     = \get_permalink( $account_page_id );
			$context['logout_link']         = \wp_logout_url( \get_permalink( $account_page_id ) );
			$context['cart_link']           = \function_exists( 'wc_get_cart_url' ) ? \wc_get_cart_url() : null;
			$context['checkout_link']       = \function_exists( 'wc_get_checkout_url' ) ? \wc_get_checkout_url() : null;
			$context['cart_contents_count'] = ( \function_exists( 'WC' ) && \WC()->cart )
				? \WC()->cart->get_cart_contents_count()
				: 0;
		}

		return $context;
	}

}
