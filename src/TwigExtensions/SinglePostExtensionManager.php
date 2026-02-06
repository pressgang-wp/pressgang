<?php

namespace PressGang\TwigExtensions;

use PressGang\Post;
use Timber\Timber;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * Registers get_latest_posts() and get_related_posts() Twig functions on single
 * post pages. Requires the post to be mapped to PressGang\Post via TimberClassMap.
 *
 * @see https://timber.github.io/docs/v2/guides/extending-twig/
 */
class SinglePostExtensionManager implements TwigExtensionManagerInterface {

	use HasNoGlobals;
	use HasNoFilters;

	/**
	 * Adds functions related to single posts to the Twig environment.
	 *
	 * This method first checks if the current request is for a single post page.
	 *
	 * If so and the post has been mapped to the PressGang\Post class it registers
	 * Twig functions for fetching the latest posts and related posts.
	 *
	 * @param Environment $twig The Twig environment where the functions will be added.
	 */
	#[\Override]
	public function add_twig_functions( Environment $twig ): void {
		if ( \is_single() ) {
			$twig->addFunction( new TwigFunction( 'get_latest_posts', function ( ?int $posts_per_page = null ): array {
				$post = Timber::get_post();

				return is_a( $post, Post::class ) ? $post->get_latest_posts( $posts_per_page ) : [];
			} ) );
			$twig->addFunction( new TwigFunction( 'get_related_posts', function ( ?int $posts_per_page = null ): array {
				$post = Timber::get_post();

				return is_a( $post, Post::class ) ? $post->get_related_posts( $posts_per_page ) : [];
			} ) );
		}
	}
}
