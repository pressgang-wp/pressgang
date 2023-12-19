<?php

namespace PressGang;

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
	 * get_taxonomy
	 *
	 * @return mixed
	 */
	protected function get_taxonomy() {
		if ( empty( $this->taxonomy ) ) {
			$this->taxonomy = Timber::get_term( get_queried_object() );
		}

		return $this->taxonomy;
	}

	/**
	 * get_context
	 *
	 * @return mixed
	 */
	protected function get_context() {
		parent::get_context();
		$this->context['taxonomy'] = $this->get_taxonomy();

		return $this->context;
	}
}