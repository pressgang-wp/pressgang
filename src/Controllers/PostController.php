<?php

namespace PressGang\Controllers;

/**
 * Controller for single post views. Extends PageController and determines the
 * Twig template based on the current post type (e.g. single.twig, single-product.twig).
 */
class PostController extends PageController {

	/** @var string */
	protected string $post_type;

	/**
	 * Infers the template from the current post type if none is provided.
	 *
	 * @param string|null $template
	 */
	public function __construct( string|null $template = null ) {

		$this->post_type = \get_post_type();

		if ( ! $template ) {
			$post_type_slug = str_replace( '_', '-', $this->post_type );
			$template       = sprintf( "single%s.twig", $post_type_slug === 'post' ? '' : "-{$post_type_slug}" );
		}

		parent::__construct( $template );
	}

	/**
	 * Adds the post to context under both 'post' and the post type key.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_context(): array {
		$post = $this->get_post();

		$this->context['post']             = $post;
		$this->context[ $this->post_type ] = $post;

		return $this->context;
	}

}
