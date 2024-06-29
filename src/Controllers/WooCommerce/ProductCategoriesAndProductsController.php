<?php

namespace PressGang\Controllers\WooCommerce;

use PressGang\Controllers\PostsController;

/**
 * Class ProductCategoriesAndProductsController
 *
 * Controller for WooCommerce products and product categories, extending the base PostsController.
 * Provides functionality for rendering category archives with specific enhancements for product categories,
 * such as thumbnail images and sidebar inclusion.
 *
 * @package PressGang
 */
class ProductCategoriesAndProductsController extends PostsController {

	use HasShopSidebar;
	use HasProductCategories;
	use HasProducts;

	/**
	 * ProductCategoriesAndProductsController constructor.
	 *
	 * Initializes the controller for WooCommerce products and product categories with a specified template.
	 *
	 * @param string|null $template The template file to use for rendering the product category archive. Defaults to 'woocommerce/archive.twig'.
	 */
	public function __construct( string|null $template = 'woocommerce/archive.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Retrieves and prepares the context data for rendering the product category archive.
	 *
	 * Extends the base get_context method from TaxonomyController, adding specific data like product categories,
	 * shop sidebar, and shop page display settings to the context for use in the Twig template.
	 *
	 * @return array The context array with additional data for the product category archive.
	 */
	protected function get_context(): array {
		parent::get_context();

		$this->context['products']           = $this->get_products();
		$this->context['product_categories'] = $this->get_product_categories();
		$this->context['shop_sidebar']       = $this->get_sidebar();
		$this->context['shop_page_display']  = \get_option( 'woocommerce_shop_page_display' );

		return $this->context;
	}
}
