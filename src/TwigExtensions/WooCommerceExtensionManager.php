<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;

/**
 * Class WooCommerceExtensionManager
 *
 * Implements TwigExtensionManagerInterface to add WooCommerce-specific Twig functions to the Twig environment.
 * This class checks for WooCommerce's existence and adds relevant Twig functions accordingly.
 *
 * @package PressGang\TwigExtensions
 */
class WooCommerceExtensionManager implements TwigExtensionManagerInterface {

	use HasNoGlobals;

	/**
	 * Adds WooCommerce specific functions to the Twig environment.
	 *
	 * This method checks if WooCommerce is active and, if so, registers a Twig function
	 * that sets the global product variable based on the current post.
	 *
	 * @param Environment $twig The Twig environment where the functions will be added.
	 */
	public function add_twig_functions( Environment $twig ): void {
		if ( class_exists( 'WooCommerce' ) ) {
			$twig->add_function( [ $this, 'timber_set_product' ] );
		}
	}

	/**
	 * Sets the global WooCommerce product based on the given post.
	 *
	 * This function is intended to be used as a Twig function. It sets the global $product variable
	 * to the WooCommerce product associated with the given post.
	 *
	 * See Timber docs for an explanation:
	 *  - "For some reason, products in the loop don’t get the right context by default."
	 *
	 * @see https://timber.github.io/docs/v2/guides/woocommerce/#tease-product
	 *
	 * @param $post The post object to set the WooCommerce product for.
	 */
	public function timber_set_product( $post ): void {
		global $product;

		if ( is_woocommerce() ) {
			$product = wc_get_product( $post->ID );
		}
	}

}
