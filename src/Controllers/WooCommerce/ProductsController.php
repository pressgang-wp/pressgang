<?php

namespace PressGang\Controllers\WooCommerce;

use PressGang\Controllers\PostsController;

/**
 * Class ProductsController
 *
 * Controller for handling WooCommerce product archive pages.
 * Extends the PostsController to add specific functionalities for WooCommerce product listings.
 *
 * @package PressGang
 */
class ProductsController extends PostsController {

	use HasShopSidebar;
	use HasProducts;

	/**
	 * ProductsController constructor.
	 *
	 * Initializes the controller for handling WooCommerce product archives with a specified template.
	 *
	 * @param string $template The template file to use for rendering the product archive. Defaults to 'woocommerce/archive.twig'.
	 */
	public function __construct( $template = 'woocommerce/archive.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Get the context for the template rendering.
	 *
	 * Extends the base get_context method from AbstractController, adding specific data
	 * like products, widget sidebar, and shop page display options to the context for rendering in the template.
	 *
	 * @return array The context array with additional data for the product archive page.
	 */
	protected function get_context(): array {
		parent::get_context();

		$this->context['products']          = $this->get_products();
		$this->context['shop_sidebar']      = $this->get_sidebar();
		$this->context['shop_page_display'] = \get_option( 'woocommerce_shop_page_display' );

		return $this->context;
	}
}
