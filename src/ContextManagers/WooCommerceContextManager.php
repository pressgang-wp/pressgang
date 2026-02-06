<?php

namespace PressGang\ContextManagers;

/**
 * Adds WooCommerce account, cart, and checkout links to the global context when
 * WooCommerce is active. Links are cached via wp_cache; cart count is always fresh.
 */
class WooCommerceContextManager implements ContextManagerInterface {

	/**
	 * @param array<string, mixed> $context
	 *
	 * @return array<string, mixed>
	 */
	#[\Override]
	public function add_to_context( array $context ): array {

		if ( class_exists( 'WooCommerce' ) ) {
			$links = \wp_cache_get( 'pressgang_wc_links' );

			if ( false === $links ) {
				$account_page_id = \get_option( 'woocommerce_myaccount_page_id' );
				$account_link    = \get_permalink( $account_page_id );

				$links = [
					'my_account_link' => $account_link,
					'logout_link'     => \wp_logout_url( $account_link ),
					'cart_link'       => \function_exists( 'wc_get_cart_url' ) ? \wc_get_cart_url() : null,
					'checkout_link'   => \function_exists( 'wc_get_checkout_url' ) ? \wc_get_checkout_url() : null,
				];

				\wp_cache_set( 'pressgang_wc_links', $links );
			}

			$context = array_merge( $context, $links );

			$context['cart_contents_count'] = ( \function_exists( 'WC' ) && \WC()->cart )
				? \WC()->cart->get_cart_contents_count()
				: 0;
		}

		return $context;
	}

}
