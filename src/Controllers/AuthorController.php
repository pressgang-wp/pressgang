<?php

namespace PressGang\Controllers;

use Override;
use Timber\PostCollectionInterface;
use Timber\Timber;
use Timber\User;

/**
 * Controller for author archive pages. Retrieves the queried author as a Timber
 * User and their paginated posts, and adds both to the template context.
 */
class AuthorController extends AbstractController {

	protected ?User $author = null;

	protected ?PostCollectionInterface $posts = null;

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
		if ( $this->author === null ) {
			$id = get_queried_object_id();
			if ( $id ) {
				$this->author = Timber::get_user( $id );
			}
		}

		return $this->author;
	}

	/**
	 * Returns the author's posts, lazily initialised. Empty array when the
	 * queried object has no author (e.g. current user is not a post author).
	 *
	 * @return PostCollectionInterface|array<int, never>|null
	 */
	protected function get_posts(): PostCollectionInterface|array|null {
		if ( $this->posts === null ) {
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
