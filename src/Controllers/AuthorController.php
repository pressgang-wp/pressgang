<?php

namespace PressGang\Controllers;

use Override;
use Timber\Timber;
use Timber\User;

/**
 * Controller for author archive pages. Adds the queried author as a Timber User
 * alongside the inherited listing context.
 *
 * The main query on an author archive already *is* that author's posts, ordered
 * and paginated by WordPress and open to `pre_get_posts`, so the listing comes
 * from PostsController rather than a second query of our own.
 */
class AuthorController extends PostsController {

	protected ?User $author = null;

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
			$id = \get_queried_object_id();

			if ( $id ) {
				$this->author = Timber::get_user( $id );
			}
		}

		return $this->author;
	}

	/**
	 * Adds the author to the inherited listing context.
	 *
	 * @return array<string, mixed>
	 */
	#[Override]
	protected function get_context(): array {
		$this->context           = parent::get_context();
		$this->context['author'] = $this->get_author();

		return $this->context;
	}
}
