<?php

namespace PressGang\Controllers;

use Timber\Term;
use Timber\Timber;

/**
 * Controller for taxonomy archive pages — categories, tags, and custom
 * taxonomies. Extends PostsController to add the current Timber Term to the
 * context alongside the standard posts/pagination data.
 *
 * When no template is given, the query-context inference applies:
 * `category.twig` / `tag.twig` / `taxonomy-{taxonomy}.twig`, each falling
 * back through the hierarchy to `archive.twig` (see PostsController::infer_template()).
 */
class TaxonomyController extends PostsController {

	/** @var Term|null */
	protected ?Term $term = null;

	/**
	 * @param string|array<int, string>|null $template
	 */
	public function __construct( string|array|null $template = null ) {
		parent::__construct( $template );
	}

	/**
	 * Returns the current term, lazily initialised via Timber.
	 *
	 * @return Term
	 */
	protected function get_term(): Term {
		if ( $this->term === null ) {
			$this->term = Timber::get_term( \get_queried_object() );
		}

		return $this->term;
	}

	/**
	 * Adds the current term to the parent archive context.
	 *
	 * @return array<string, mixed>
	 */
	#[\Override]
	protected function get_context(): array {
		$this->context = parent::get_context();
		$this->context['term'] = $this->get_term();

		return $this->context;
	}
}
