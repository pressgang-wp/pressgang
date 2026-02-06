<?php

namespace PressGang\Controllers;

/**
 * Controller for search results. Extends PostsController and overrides the page
 * title to include the search query (e.g. "Search results for 'foo'").
 */
class SearchController extends PostsController {

	/**
	 * @param string|null $template
	 */
	public function __construct( string|null $template = 'search.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Generates a "Search results for '...'" page title.
	 *
	 * @return string
	 */
	protected function get_page_title(): string {

		if ( empty( $this->page_title ) ) {
			$this->page_title = sprintf( \_x( "Search results for '%s'", THEMENAME ), 'Search', \get_search_query() );
		}

		return $this->page_title;
	}
}
