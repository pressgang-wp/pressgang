<?php

/**
 * Class SeoTitle
 *
 * Handles the modification of the WordPress title tag for SEO purposes.
 *
 * Makes some changes to the <title> tag, by filtering the output of wp_title().
 *
 * If we have a site description, and we're viewing the home page or a blog posts
 * page (when using a static front page), then we will add the site description.
 *
 * If we're viewing a search result, then we're going to recreate the title entirely.
 * We're going to add page numbers to all titles as well, to the middle of a search
 * result title and the end of all other titles.
 *
 * The site title also gets added to all titles.
 *
 */
class SeoTitle {

	/**
	 * Constructor.
	 *
	 * Adds a filter to modify the WordPress title tag.
	 */
	public function __construct() {
		\add_filter( 'wp_title', [ $this, 'filter_wp_title' ], 10, 3 );
	}

	/**
	 * Filters and modifies the WordPress title tag.
	 *
	 * @param string $title The original title.
	 * @param string $separator The title separator.
	 * @param string $location The location of the site name (left or right).
	 *
	 * @return string The modified title.
	 */
	public function filter_wp_title( $title, $separator, $location ) {
		$separator = trim( $separator );
		$separator = $separator ? " {$separator} " : ' ';

		if ( \is_feed() ) {
			return $title;
		}

		if ( \is_search() ) {
			return $this->get_search_title( $title, $separator );
		}

		return $this->get_standard_title( $title, $separator, $location );
	}

	/**
	 * Generates a title for search result pages.
	 *
	 * @param string $title The original title.
	 * @param string $separator The title separator.
	 *
	 * @return string The modified title for search pages.
	 */
	protected function get_search_title( $title, $separator ) {
		global $paged;

		$title = sprintf( "%s '%s'", \_x( "Search", 'Title', THEMENAME ), \get_search_query() );

		if ( $paged >= 2 ) {
			$title .= $separator . $paged;
		}

		$title .= $separator . \get_bloginfo( 'name', 'display' );

		return $title;
	}

	/**
	 * Generates a standard title for pages other than search results.
	 *
	 * @param string $title The original title.
	 * @param string $separator The title separator.
	 * @param string $location The location of the site name.
	 *
	 * @return string The modified standard title.
	 */
	protected function get_standard_title( string $title, string $separator, string $location ): string {
		global $paged, $page;

		switch ( strtolower( $location ) ) {
			case 'left':
				$title = \get_bloginfo( 'name', 'display' ) . $title;
				break;
			case 'right':
				$title .= \get_bloginfo( 'name', 'display' );
				break;
			default:
				$title .= $separator . \get_bloginfo( 'name', 'display' );
		}

		if ( $this->is_front_page_or_home() ) {
			$title .= $separator . $this->get_site_description();
		}

		if ( $this->has_multiple_pages( $paged, $page ) ) {
			$title .= $separator . max( $paged, $page );
		}

		return trim( $title );
	}

	/**
	 * Checks if it's the front page or home.
	 *
	 * @return bool True if it's the front page or home, false otherwise.
	 */
	protected function is_front_page_or_home() {
		return \is_home() || \is_front_page();
	}

	/**
	 * Retrieves the site description.
	 *
	 * @return string The site description.
	 */
	protected function get_site_description() {
		return \get_bloginfo( 'description', 'display' );
	}

	/**
	 * Checks if there are multiple pages.
	 *
	 * @param int $paged Current page number.
	 * @param int $page Current page number.
	 *
	 * @return bool True if there are multiple pages, false otherwise.
	 */
	protected function has_multiple_pages( $paged, $page ) {
		return ( is_numeric( $paged ) && $paged >= 2 ) || ( is_numeric( $page ) && $page >= 2 );
	}
}

new SeoTitle();
