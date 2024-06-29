<?php

namespace PressGang\TwigExtensions;

use PressGang\Post;
use Timber\Timber;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * Class SinglePostExtensionManager
 *
 * Implements the TwigExtensionManagerInterface to add single post related functions to the Twig environment.
 * This class focuses on providing functionalities specific to single post pages in a WordPress context.
 *
 * @see https://timber.github.io/docs/v2/guides/extending-twig/#adding-functionality-with-the-twig-environment-filter
 * @package PressGang\TwigExtensions
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
	public function add_twig_functions( Environment $twig ): void {
		if ( \is_single() ) {
			$post = Timber::get_post();
			if ( is_a( $post, Post::class ) ) {
				$twig->addFunction( new TwigFunction( 'get_latest_posts', [ $post, 'get_latest_posts' ] ) );
				$twig->addFunction( new TwigFunction( 'get_related_posts', [ $post, 'get_related_posts' ] ) );
			}
		}
	}
}
