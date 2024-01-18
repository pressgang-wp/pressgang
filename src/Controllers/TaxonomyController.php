<?php

namespace PressGang\Controllers;

use Timber\Term;
use Timber\Timber;

/**
 * Class TaxonomyController
 *
 * Controller for handling taxonomy-related pages in a WordPress theme.
 * Extends the basic PostsController to add specific functionalities for taxonomies.
 *
 * @package PressGang
 */
class TaxonomyController extends PostsController {

	/**
	 * The cached current Term object.
	 *
	 * @var Term
	 */
	protected Term $term;

	/**
	 * TaxonomyController constructor.
	 *
	 * Initializes the controller for handling taxonomies with a specified template and post type.
	 *
	 * @param string $template The template file to use for rendering the taxonomy. Defaults to 'taxonomy.twig'.
	 * @param string|null $post_type The post type associated with the taxonomy. Defaults to null.
	 */
	public function __construct( $template = 'taxonomy.twig', $post_type = null ) {
		parent::__construct( $template, $post_type );
	}

	/**
	 * Get the current term object.
	 *
	 * Retrieves the current term object using Timber. Caches the term object after the first retrieval.
	 *
	 * @return mixed The term object for the current taxonomy term.
	 */
	protected function get_term(): Term {
		if ( empty( $this->term ) ) {
			$this->term = Timber::get_term( \get_queried_object() );
		}

		return $this->term;
	}

	/**
	 * Get the context for the taxonomy template rendering.
	 *
	 * Extends the base get_context method from PostsController, adding the current term object
	 * to the context for use in the template.
	 *
	 * @return array The context array with additional data for the taxonomy term.
	 */
	protected function get_context(): array {
		parent::get_context();
		$this->context['term'] = $this->get_term();

		return $this->context;
	}
}
