<?php

namespace PressGang\Controllers\WooCommerce;

use PressGang\Controllers\TaxonomyController;

/**
 * Class ProductCategoryController
 *
 * Controller for WooCommerce product categories, extending the base TaxonomyController.
 * This class provides WooCommerce-specific functionality for category archives.
 *
 * @package PressGang
 */
class ProductCategoryController extends TaxonomyController {

	/**
	 * ProductCategoryController constructor.
	 *
	 * Initializes the controller for WooCommerce product categories with a specified template.
	 *
	 * @param string|null $template The template file to use for rendering the product category archive. Defaults to 'woocommerce/archive.twig'.
	 */
	public function __construct( string|null $template = 'woocommerce/archive.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Retrieves and prepares the context data for rendering the product category archive.
	 *
	 * This method extends the base get_context method, adding specific data like the current category
	 * and the category title to the context for rendering in the Twig template.
	 *
	 * @return array The context array with additional data for the product category archive.
	 */
	protected function get_context(): array {
		parent::get_context();

		$this->context['category'] = $this->get_term();
		$this->context['title']    = \single_term_title( '', false );

		return $this->context;
	}
}
