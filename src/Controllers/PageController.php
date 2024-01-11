<?php

namespace PressGang\Controllers;

use Timber\Post;
use Timber\Timber;

/**
 * PageController class responsible for handling the display logic of a standard WordPress page.
 *
 * Extends the AbstractController to utilize common functionalities like context management
 * and rendering of Twig templates using the Timber library. This controller specifically
 * handles the retrieval and preparation of page-related data for rendering.
 */
class PageController extends AbstractController {

	/**
	 * The current post object.
	 *
	 * @var Post
	 */
	protected Post $post;

	/**
	 * Constructor for the PageController class.
	 *
	 * Initializes the controller with the specified Twig template, or defaults to 'page.twig'.
	 *
	 * @param string $template The path or name of the Twig template to be rendered. Defaults to 'page.twig'.
	 */
	public function __construct( $template = 'page.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Retrieves the current post object using Timber.
	 *
	 * Lazily loads and stores the post object for use in the context.
	 *
	 * @return Post The current post object.
	 */
	protected function get_post(): Post {
		if ( empty( $this->post ) ) {
			$this->post = Timber::get_post();
		}

		return $this->post;
	}

	/**
	 * Retrieves the context for rendering the page.
	 *
	 * Overrides the base get_context method to add the current post object to the context.
	 * Ensures that the page and post data are available in the Twig template.
	 *
	 * @return array The modified context array with page and post data.
	 */
	protected function get_context(): array {
		$post                  = $this->get_post();
		$this->context['page'] = $post;
		$this->context['post'] = $post;

		return $this->context;
	}
}
