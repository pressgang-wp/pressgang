<?php

/**
 * Class OpenGraph
 *
 * Manages the Open Graph meta tags for social media sharing.
 *
 * @see https://developers.facebook.com/docs/sharing/webmasters/#markup
 * @package PressGang
 */
class OpenGraph {

	/**
	 * Constructor.
	 *
	 * Adds the Open Graph meta tags to the wp_head action.
	 */
	public function __construct() {
		\add_action( 'wp_head', [ $this, 'add_meta_tags' ], 5 );
	}

	/**
	 * Outputs the Open Graph meta tags.
	 *
	 * Gathers all necessary data and uses Timber to render the Open Graph meta tags.
	 */
	public function add_meta_tags() {
		Timber::render( 'partials/open-graph.twig', [
			'site_name'   => \apply_filters( 'pressgang_og_site_name', \get_bloginfo() ),
			'title'       => \apply_filters( 'pressgang_og_title', $this->get_title() ),
			'description' => \apply_filters( 'pressgang_og_description', $this->get_description() ),
			'type'        => \apply_filters( 'pressgang_og_type', $this->get_type() ),
			'url'         => \apply_filters( 'pressgang_og_url', $this->get_url() ),
			'image'       => \apply_filters( 'pressgang_og_image', $this->get_image_url() ),
		] );
	}

	/**
	 * Retrieves the URL of the Open Graph image.
	 *
	 * @return string The image URL.
	 */
	protected function get_image_url(): string {
		$post    = \Timber::get_post();
		$img_url = $post && \has_post_thumbnail( $post->ID )
			? \wp_get_attachment_image_src( \get_post_thumbnail_id( $post->ID ), 'large' )[0]
			: ( \get_theme_mod( 'logo' ) );

		return \apply_filters( 'og_image', $img_url );
	}

	/**
	 * Determines the Open Graph type (e.g., article, website).
	 *
	 * @return string The Open Graph type.
	 */
	protected function get_type(): string {
		return \is_author() ? 'profile' : ( is_single() ? 'article' : 'website' );
	}

	/**
	 * Retrieves the description for the Open Graph meta tag.
	 *
	 * @return string The description.
	 */
	protected function get_description(): string {
		return PressGang\SEO\MetaDescriptionService::get_meta_description();
	}

	/**
	 * Retrieves the title for the Open Graph meta tag.
	 *
	 * @return string The title.
	 */
	protected function get_title(): string {
		if ( \is_tax() ) {
			return \single_term_title( '', false );
		} elseif ( \is_post_type_archive() ) {
			return \get_the_archive_title();
		}

		return \get_the_title();
	}

	/**
	 * Retrieves the URL for the Open Graph meta tag.
	 *
	 * @return string The URL.
	 */
	protected function get_url(): string {
		if ( \is_tax() ) {
			return \get_term_link( \get_query_var( 'term' ), \get_query_var( 'taxonomy' ) );
		} elseif ( \is_post_type_archive() ) {
			return \get_post_type_archive_link( \get_query_var( 'post_type' ) );
		}

		return \get_permalink();
	}
}

new OpenGraph();
