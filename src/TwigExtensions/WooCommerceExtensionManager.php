<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * Class WooCommerceExtensionManager
 *
 * Implements TwigExtensionManagerInterface to add WooCommerce-specific Twig functions to the Twig environment.
 * This class checks for WooCommerce's existence and adds relevant Twig functions accordingly.
 *
 * @see https://timber.github.io/docs/v2/guides/extending-twig/#adding-functionality-with-the-twig-environment-filter
 * @package PressGang\TwigExtensions
 */
class WooCommerceExtensionManager implements TwigExtensionManagerInterface {

	use HasNoGlobals;
	use HasNoFilters;

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
			$twig->addFunction( new TwigFunction( 'timber_set_product', [ $this, 'timber_set_product' ] ) );
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
	 * @param $post
	 *
	 * @return \WC_Product|null
	 */
	public function timber_set_product( $post ): \WC_Product|null {
		// Check if the post object is valid and its post type is 'product'
		if ( ! $post || ! isset( $post->ID ) || get_post_type( $post->ID ) !== 'product' ) {
			return null;
		}

		// Set the global product
		global $product;

		// Retrieve and return the WC_Product object
		$product = \wc_get_product( $post->ID );

		return $product;
	}
}
