<?php

namespace PressGang\Controllers\WooCommerce;

use PressGang\TaxonomyController;

/**
 * Class ProductsController
 *
 * @package PressGang
 */
class ProductCategoryController extends TaxonomyController {

	protected $category = null;

	/**
	 * __construct
	 *
	 * WCProductController constructor
	 *
	 * @param string $template
	 */
	public function __construct( $template = 'woocommerce/archive.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * get_context
	 *
	 */
	public function get_context() {
		parent::get_context();

		$this->context['category'] = $this->get_taxonomy();
		$this->context['title']    = single_term_title( '', false );

		return $this->context;
	}
}
