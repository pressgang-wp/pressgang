<?php

namespace PressGang\Controllers;

use Timber\Timber;

/**
 * Controller for author archive pages. Retrieves the queried author as a Timber
 * User and their paginated posts, and adds both to the template context.
 */
class AuthorController extends AbstractController {

	/** @var \Timber\User|null */
	protected mixed $author;

	/** @var \Timber\Post[]|null */
	protected mixed $posts;

	/**
	 * @param string|null $template
	 */
	public function __construct( string|null $template = 'author.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Returns the queried author as a Timber User, lazily initialised.
	 *
	 * @return \Timber\User|null
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
	 * Returns the author's posts, lazily initialised.
	 *
	 * @return \Timber\Post[]|null
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
	 * Adds the author and their posts to the context.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_context(): array {
		$this->context['author'] = $this->get_author();
		$this->context['posts']  = $this->get_posts();

		return $this->context;
	}
}
