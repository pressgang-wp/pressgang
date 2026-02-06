<?php

namespace PressGang\Controllers;

use Timber\Post;
use Timber\Timber;

/**
 * Controller for standard WordPress pages. Retrieves the current post via Timber
 * and adds it to the context as both 'page' and 'post'. Defaults to page.twig.
 */
class PageController extends AbstractController {

	/** @var Post */
	protected Post $post;

	/**
	 * @param string|null $template
	 */
	public function __construct( string|null $template = 'page.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Returns the current post, lazily initialised via Timber.
	 *
	 * @return Post
	 */
	protected function get_post(): Post {
		if ( empty( $this->post ) ) {
			$this->post = Timber::get_post();
		}

		return $this->post;
	}

	/**
	 * Adds the current post to context as both 'page' and 'post'.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_context(): array {
		$post                  = $this->get_post();
		$this->context['page'] = $post;
		$this->context['post'] = $post;

		return $this->context;
	}
}
