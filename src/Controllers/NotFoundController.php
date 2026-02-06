<?php

namespace PressGang\Controllers;

/**
 * Controller for 404 (Not Found) pages. Extends PageController and provides
 * translatable default title and content values. Defaults to 404.twig.
 */
class NotFoundController extends PageController {

	/**
	 * @param string|null $template
	 */
	public function __construct( string|null $template = '404.twig' ) {
		parent::__construct( $template );
	}


	/**
	 * Adds translatable title and content for the 404 page.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_context(): array {
		$this->context['title']   = _x( "Not Found", 'Templates', THEMENAME );
		$this->context['content'] = _x( "Sorry, we couldn't find what you were looking for!", 'Templates', THEMENAME );

		return $this->context;
	}
}
