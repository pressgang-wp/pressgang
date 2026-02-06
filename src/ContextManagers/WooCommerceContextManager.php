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

		if ( $this->is_woocommerce_active() ) {
			$links = \wp_cache_get( 'pressgang_wc_links' );

			if ( false === $links ) {
				$links = $this->build_links();
				\wp_cache_set( 'pressgang_wc_links', $links );
			}

			$context = array_merge( $context, $links );

			$context['cart_contents_count'] = $this->get_cart_contents_count();
		}

		return $context;
	}

	/**
	 * Checks whether WooCommerce is active.
	 *
	 * @return bool
	 */
	protected function is_woocommerce_active(): bool {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Builds the WooCommerce links array.
	 *
	 * @return array<string, string|null>
	 */
	protected function build_links(): array {
		$account_page_id = \get_option( 'woocommerce_myaccount_page_id' );
		$account_link    = \get_permalink( $account_page_id );

		return [
			'my_account_link' => $account_link,
			'logout_link'     => \wp_logout_url( $account_link ),
			'cart_link'       => \function_exists( 'wc_get_cart_url' ) ? \wc_get_cart_url() : null,
			'checkout_link'   => \function_exists( 'wc_get_checkout_url' ) ? \wc_get_checkout_url() : null,
		];
	}

	/**
	 * Returns the current cart item count, or 0 if WC() is unavailable.
	 *
	 * @return int
	 */
	protected function get_cart_contents_count(): int {
		return ( \function_exists( 'WC' ) && \WC()->cart )
			? \WC()->cart->get_cart_contents_count()
			: 0;
	}

}
