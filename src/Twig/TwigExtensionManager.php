<?php

namespace PressGang\Twig;

use PressGang\Post;
use Twig\TwigFunction;
use PressGang\SEO\MetaDescriptionService;

/**
 * Class TwigExtensionManager
 *
 * Manages the addition of functions and global variables to the Twig environment in a WordPress theme.
 * This class systematically adds standard and custom Twig functions and globals,
 * allowing for extension or modification via WordPress filters.
 *
 * @package PressGang\Twig
 */
class TwigExtensionManager {

	/**
	 * Adds functions and global variables to the Twig environment.
	 *
	 * This method retrieves a list of default Twig functions, widget Twig functions, and global variables,
	 * allowing them to be filtered before adding them to the Twig environment.
	 *
	 * @param \Twig\Environment $twig The Twig environment.
	 *
	 * @return \Twig\Environment The modified Twig environment with added functions and globals.
	 */
	public function add_to_twig( $twig ) {
		$this->add_twig_globals( $twig );
		$this->add_twig_functions( $twig );

		return $twig;
	}

	/**
	 * Adds global variables to the Twig environment.
	 *
	 * @param \Twig\Environment $twig The Twig environment.
	 *
	 * @return \Twig\Environment The Twig environment with added global variables.
	 */
	protected function add_twig_globals( $twig ) {
		$twig->addGlobal( 'THEMENAME', defined( 'THEMENAME' ) ? THEMENAME : 'pressgang' );

		return $twig;
	}

	/**
	 * Adds standard and widget-specific Twig functions to the environment.
	 *
	 * @param \Twig\Environment $twig The Twig environment.
	 *
	 * @return \Twig\Environment The Twig environment with added functions.
	 */
	protected function add_twig_functions( $twig ) {
		$functions = array_merge(
			$this->get_default_twig_functions(),
			$this->get_widget_twig_functions()
		);

		if ( is_single() ) {
			$post = Post::get_post();
			$this->get_post_related_twig_functions( $post );
		}

		$functions = apply_filters( 'pressgang_twig_functions', $functions );

		foreach ( $functions as $function ) {
			$twig->addFunction( $function );
		}

		return $twig;
	}

	/**
	 * Retrieves the default set of Twig functions to be added to the environment.
	 *
	 * @return array An array of TwigFunction objects.
	 */
	protected function get_default_twig_functions() {
		return [
			new TwigFunction( 'get_search_query', 'get_search_query' ),
			new TwigFunction( 'get_option', 'get_option' ),
			new TwigFunction( 'get_theme_mod', 'get_theme_mod' ),
			new TwigFunction( 'meta_description', [ MetaDescriptionService::class, 'get_meta_description' ] ),
		];
	}

	/**
	 * Retrieves Twig functions for registered widget areas.
	 *
	 * Creates a Twig function for each registered sidebar, allowing widgets in these areas to be rendered in Twig templates.
	 *
	 * @return array An array of TwigFunction objects for widget areas.
	 */
	protected function get_widget_twig_functions() {

		$functions = [];
		global $wp_registered_sidebars;

		foreach ( $wp_registered_sidebars as $key => $sidebar ) {

			$functions[] = new TwigFunction( "widget_{$key}", function () use ( $sidebar ) {
				return \Timber::get_widgets( $sidebar['id'] );
			} );
		}

		return $functions;
	}

	/**
	 * Add functions for the single posts
	 *
	 * @param Post $post
	 *
	 * @return TwigFunction[]
	 */
	protected function get_post_related_twig_functions( Post $post ) {
		return [
			new TwigFunction( 'get_latest_posts', [ $post, 'get_latest_posts' ] ),
			new TwigFunction( 'get_related_posts', [ $post, 'get_related_posts' ] ),
		];
	}

}
