<?php

namespace PressGang\Controllers;

/**
 * Extends the PostsController class and overrides the title
 *
 * @package PressGang
 */
class SearchController extends PostsController {

	/**
	 * SearchController constructor
	 *
	 * Adds a default $template 'search.twig'
	 *
	 * @param string $template
	 */
	public function __construct( $template = 'search.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Automatically generates the search page title
	 *
	 * @return string
	 */
	protected function get_page_title(): string {

		if ( empty( $this->page_title ) ) {
			$this->page_title = sprintf( \_x( "Search results for '%s'", THEMENAME ), 'Search', get_search_query() );
		}

		return $this->page_title;
	}
}
