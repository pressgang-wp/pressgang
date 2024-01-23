<?php

namespace PressGang\Controllers;

/**
 * Class PostController
 *
 * Controller for handling single post views in WordPress. It extends the PageController
 * and adds functionalities specific to individual posts.
 *
 * @package PressGang
 */
class PostController extends PageController {

	/**
	 * @var string
	 */
	protected string $post_type;

	/**
	 * PostController constructor.
	 *
	 * Initializes the PostController with a specific template and post type.
	 * Determines the template to use based on the post type and sets up the
	 * post data for the controller context.
	 *
	 * @param string|null $template Optional Twig template file name for rendering the post.
	 *                              Defaults to a template based on the post type.
	 * @param string|null $post_type Optional post type. Defaults to the current post type.
	 */
	public function __construct( string|null $template = 'single.twig' ) {

		$this->post_type = \get_post_type();

		if ( ! $template ) {
			$post_type_slug = str_replace( '_', '-', $this->post_type );
			$template       = sprintf( "single%s.twig", $post_type_slug === 'post' ? '' : "-{$post_type_slug}" );
		}

		parent::__construct( [ $template, 'single.twig' ] );
	}

	/**
	 * Retrieves and prepares the context for rendering the post view.
	 *
	 * Adds the $post object to 'post' and '$post_type' context variables.
	 *
	 * @return array The modified context array with post and related data.
	 */
	protected function get_context(): array {
		$post = $this->get_post();

		$this->context['post']             = $post;
		$this->context[ $this->post_type ] = $post;

		return $this->context;
	}

}
