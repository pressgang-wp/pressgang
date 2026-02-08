<?php

namespace PressGang\Controllers;

use Override;
use Timber\Post;
use Timber\Timber;

/**
 * Controller for rendering the comments template. Enqueues the comment-reply
 * script when threaded comments are enabled and adds the current post to context.
 */
class CommentsController extends AbstractController {

	/** @var Post|null */
	protected ?Post $post;

	/**
	 * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/#comment-reply-script
	 *
	 * @param string|null $template
	 * @return void
	 */
	public function __construct( string|null $template = 'comments.twig' ) {
		parent::__construct( $template );

		// allow comment replies, see https://codex.wordpress.org/Function_Reference/comment_reply_link
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	 * Returns the current post, lazily initialised via Timber.
	 *
	 * @return Post|null
	 */
	protected function get_post(): ?Post {
		if ( empty( $this->post ) ) {
			$this->post = Timber::get_post();
		}

		return $this->post;
	}

	/**
	 * @return array<string, mixed>
	 */
	#[Override]
	protected function get_context(): array {
		$this->context['post'] = $this->get_post();

		return $this->context;
	}
}
