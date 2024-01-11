<?php

namespace PressGang\Controllers;

use Timber\Timber;

/**
 * Class TaxonomyController
 *
 * @package PressGang
 */
class TaxonomyController extends PostsController {

	protected $taxonomy;

	/**
	 * __construct
	 *
	 * PageController constructor
	 *
	 * @param string $template
	 */
	public function __construct( $template = 'taxonomy.twig', $post_type = null ) {
		parent::__construct( $template, $post_type );
	}

	/**
	 * get_term
	 *
	 * @return mixed
	 */
	protected function get_term() {
		if ( empty( $this->term ) ) {
			$this->term = Timber::get_term( get_queried_object() );
		}

		return $this->term;
	}

	/**
	 * get_context
	 *
	 * @return mixed
	 */
	protected function get_context() {
		parent::get_context();
		$this->context['term'] = $this->get_term();

		return $this->context;
	}
}
