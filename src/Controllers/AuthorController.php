<?php

namespace PressGang\Controllers;

use Timber\Timber;

/**
 * Class AuthorController
 *
 * @package PressGang
 */
class AuthorController extends AbstractController {

	protected $author;
	protected $posts;

	/**
	 * AuthorController constructor
	 *
	 * @param string $template
	 */
	public function __construct( $template = 'author.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * get_author
	 *
	 * @return mixed
	 */
	protected function get_author() {
		if ( empty( $this->author ) ) {
			if ( $id = get_queried_object_id() ) {
				$this->author = Timber::get_user( $id );
				// $this->author->thumbnail = Timber::get_image( get_avatar_url( $this->author->id ) );
			}
		}

		return $this->author;
	}

	/**
	 * get_posts
	 *
	 * @return mixed
	 */
	protected function get_posts() {

		if ( empty( $this->posts ) ) {

			$args = [
				'author' => $this->get_author()->id,
				'paged'  => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
			];

			$this->posts = Timber::get_posts( $args );
		}

		return $this->posts;
	}

	/**
	 * get_context
	 *
	 * @return mixed
	 */
	protected function get_context() {
		$this->context['author'] = $this->get_author();
		$this->context['posts']  = $this->get_posts();

		return $this->context;
	}
}
