<?php

namespace PressGang\Controllers;

use Timber\Term;
use Timber\Timber;

/**
 * Controller for taxonomy archive pages. Extends PostsController to add the
 * current Timber Term to the context alongside the standard posts/pagination data.
 */
class TaxonomyController extends PostsController {

	/** @var Term */
	protected Term $term;

	/**
	 * @param string|null $template
	 */
	public function __construct( string|null $template = 'taxonomy.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Returns the current term, lazily initialised via Timber.
	 *
	 * @return Term
	 */
	protected function get_term(): Term {
		if ( empty( $this->term ) ) {
			$this->term = Timber::get_term( \get_queried_object() );
		}

		return $this->term;
	}

	/**
	 * Adds the current term to the parent archive context.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_context(): array {
		$this->context = parent::get_context();
		$this->context['term'] = $this->get_term();

		return $this->context;
	}
}
