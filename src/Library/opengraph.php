<?php

namespace PressGang\Library;

use PressGang\Site;
use function PressGang\add_action;
use function PressGang\apply_filters;
use function PressGang\esc_attr;
use function PressGang\esc_url;
use function PressGang\get_permalink;
use function PressGang\get_post_type_archive_link;
use function PressGang\get_term_link;
use function PressGang\get_the_archive_title;
use function PressGang\get_the_title;
use function PressGang\get_theme_mod;
use function PressGang\has_post_thumbnail;
use function PressGang\is_author;
use function PressGang\is_post_type_archive;
use function PressGang\is_single;
use function PressGang\is_tax;
use function PressGang\single_term_title;
use function PressGang\wp_get_attachment_image_src;
use function PressGang\wp_strip_all_tags;

/**
 * Class OpenGraph
 *
 * @package PressGang
 */
class OpenGraph {

	/**
	 * init
	 *
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'fb_opengraph' ), 5 );
	}

	/**
	 * fb_opengraph
	 *
	 */
	public function fb_opengraph() {

		$post = \Timber::get_post();

		$img = $post && has_post_thumbnail( $post->ID )
			? wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' )[0]
			: ( get_theme_mod( 'og_img' )
				? get_theme_mod( 'og_img' )
				: esc_url( get_theme_mod( 'logo' ) ) );

		$type = is_author() ? 'profile' : ( is_single() ? 'article' : 'website' );

		$description = Site::meta_description();

		if ( is_tax() ) {
			$url   = get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			$title = single_term_title( '', false );
		} elseif ( is_post_type_archive() ) {
			$url   = get_post_type_archive_link( get_query_var( 'post_type' ) );
			$title = get_the_archive_title();
		} else {
			$url   = get_permalink();
			$title = get_the_title();
		}

		$url = rtrim( esc_url( apply_filters( 'og_url', $url ) ) );
		if ( ! substr( $url, - 1 ) === '/' ) {
			$url .= '/'; // slash fixes Facebook Debugger "Circular Redirect Path"
		}

		$open_graph = array(
			'site_name'   => apply_filters( 'og_site_name', get_bloginfo() ),
			'title'       => apply_filters( 'og_title', $title ),
			'description' => wp_strip_all_tags( apply_filters( 'og_description', $description ) ),
			'type'        => esc_attr( apply_filters( 'og_type', $type ) ),
			'url'         => esc_url( 'og_url', $url ),
			'image'       => esc_url( apply_filters( 'og_image', $img ) ),
		);

		\Timber\Timber::render( 'partials/open-graph.twig', array( 'open_graph' => $open_graph ) );
	}
}

new OpenGraph();