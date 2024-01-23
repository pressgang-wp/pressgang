<?php

namespace PressGang\Controllers\WooCommerce;

use PressGang\Controllers\PostController;

/**
 * Class ProductController
 *
 * Controller for handling single WooCommerce product pages.
 * Extends the PostController to add specific functionalities for WooCommerce products.
 *
 * @package PressGang
 */
class ProductController extends PostController {

	use HasShopSidebar;

	protected WC_Product $product;

	/**
	 * ProductController constructor.
	 *
	 * Initializes the controller for handling single product pages with a specified template.
	 *
	 * @param string|null $template The template file to use for rendering the single product. Defaults to 'woocommerce/single-product.twig'.
	 */
	public function __construct( string|null $template = 'woocommerce/single-product.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Retrieve the current WooCommerce product.
	 *
	 * Fetches and caches the current WooCommerce product object using wc_get_product.
	 * See WooCommerce documentation for more details on wc_get_product.
	 *
	 * @see https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-product-functions.html#function_wc_get_product
	 * @return WC_Product The WooCommerce product object.
	 */
	protected function get_product(): WC_Product {
		if ( empty( $this->product ) ) {
			$this->product = \wc_get_product( $this->get_post()->id );
		}

		return $this->product;
	}

	/**
	 * Get the context for the single product template rendering.
	 *
	 * Extends the base get_context method from AbstractController, adding specific data
	 * like the widget sidebar, product details, and post data to the context for rendering in the template.
	 *
	 * @return array The context array with additional data for the single product page.
	 */
	public function get_context(): array {
		parent::get_context();

		$this->context['shop_sidebar'] = $this->get_sidebar();
		$this->context['product']      = $this->get_product();

		return $this->context;
	}

}
