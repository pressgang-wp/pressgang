<?php

namespace PressGang\Controllers;

/**
 * Class NotFoundController
 *
 * Controller for handling 404 (Not Found) pages. This controller extends the PageController,
 * customizing it for the specific needs of rendering a 404 page.
 *
 * @package PressGang
 */
class NotFoundController extends PageController {

	/**
	 * NotFoundController constructor.
	 *
	 * Initializes a NotFoundController with a default template for 404 pages.
	 *
	 * @param string|null $template Optional Twig template file name for rendering the 404 page. Defaults to '404.twig'.
	 */
	public function __construct( string|null $template = '404.twig' ) {
		parent::__construct( $template );
	}


	/**
	 * Get the context for the 404 page.
	 *
	 * Prepares the context array with specific variables for the 404 page, such as title and content.
	 *
	 * @return array The context array with 404-specific values.
	 */
	protected function get_context(): array {
		$this->context['title']   = _x( "Not Found", 'Templates', THEMENAME );
		$this->context['content'] = _x( "Sorry, we couldn't find what you were looking for!", 'Templates', THEMENAME );

		return $this->context;
	}
}
