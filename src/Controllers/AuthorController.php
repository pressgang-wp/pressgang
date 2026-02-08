<?php

namespace PressGang\Controllers;

use Override;
use Timber\Post;
use Timber\PostQuery;
use Timber\Timber;
use Timber\User;

/**
 * Controller for author archive pages. Retrieves the queried author as a Timber
 * User and their paginated posts, and adds both to the template context.
 */
class AuthorController extends AbstractController {

	protected ?User $author = null;

	/** @var PostQuery|Post[]|null */
	protected mixed $posts = null;

	/**
	 * @param string|null $template
	 */
	public function __construct( string|null $template = 'author.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * Returns the queried author as a Timber User, lazily initialised.
	 *
	 * @return User|null
	 */
	protected function get_author(): ?User {
		if ( empty( $this->author ) ) {
			$id = get_queried_object_id();
			if ( $id ) {
				$this->author = Timber::get_user( $id );
			}
		}

		return $this->author;
	}

	/**
	 * Returns the author's posts, lazily initialised.
	 *
	 * @return Post[]|null
	 */
	protected function get_posts(): mixed {
		if ( empty( $this->posts ) ) {
			$author = $this->get_author();

			if ( ! $author ) {
				return [];
			}

			$args = [
				'author' => $author->id,
				'paged'  => get_query_var( 'paged' ) ?: 1,
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
	#[Override]
	protected function get_context(): array {
		$this->context['author'] = $this->get_author();
		$this->context['posts']  = $this->get_posts();

		return $this->context;
	}
}
