<?php

namespace PressGang\Library;

use PressGang\Scripts;
use function PressGang\add_action;
use function PressGang\apply_filters;
use function PressGang\check_ajax_referer;
use function PressGang\do_action;
use function PressGang\get_option;
use function PressGang\get_template_directory_uri;
use function PressGang\get_template_part;
use function PressGang\is_admin;
use function PressGang\is_archive;
use function PressGang\is_home;
use function PressGang\query_posts;
use function PressGang\wp_localize_script;

/**
 * Class InfinitePagination
 *
 * Class handles Wordpress paging and adds an infinite paginator to the theme
 *
 */
class InfinitePagination {

	public $posts_per_page;

	const AJAX_ACTION = 'infinite_pagination';

	/**
	 * init
	 *
	 */
	public function __construct() {

		$this->posts_per_page = (int) get_option( 'posts_per_page' );

		add_action( sprintf( "wp_ajax_%s", self::AJAX_ACTION ), array( $this, self::AJAX_ACTION ) ); // user logged in
		add_action( sprintf( "wp_ajax_nopriv_%s", self::AJAX_ACTION ), array(
			$this,
			self::AJAX_ACTION
		) ); // not logged in

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'pre_get_posts', array( $this, 'set_query_offset' ) );

		Scripts::$scripts['images-loaded'] = array(
			'src'  => get_template_directory_uri() . '/js/src/vendor/images-loaded/imagesloaded.pkgd.min.js',
			'deps' => array( 'jquery' ),
			'ver'  => '4.1.4',
			'hook' => 'add_infinite_pagination',
		);

		// https://github.com/Foliotek/AjaxQ
		Scripts::$scripts['ajaxq'] = array(
			'src'  => get_template_directory_uri() . '/js/src/vendor/ajaxq/ajaxq.js',
			'deps' => array( 'jquery' ),
			'ver'  => '1.0',
			'hook' => 'add_infinite_pagination',
		);

		Scripts::$scripts['infinite-pagination'] = array(
			'src'  => get_template_directory_uri() . '/js/src/custom/infinite-pagination.js',
			'deps' => array( 'ajaxq', 'images-loaded' ),
			'ver'  => '0.1.21',
			'hook' => 'add_infinite_pagination',
		);
	}

	/**
	 * infinte_pagination
	 *
	 * Filter the query with 'infinite_pagination_query'.
	 * Hook Template with action 'infinite_pagination_template'.
	 *
	 * @return void
	 */
	public function infinite_pagination() {
		check_ajax_referer( self::AJAX_ACTION );

		$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );
		$paged     = filter_input( INPUT_POST, 'page_no', FILTER_SANITIZE_NUMBER_INT );

		$query = array(
			'paged'          => $paged,
			'posts_per_page' => $this->posts_per_page,
			'post_type'      => $post_type,
			'post_status'    => 'publish',
		);

		// apply custom search terms?
		$query = apply_filters( 'infinite_pagination_query', $query );

		// load the posts
		query_posts( $query );

		global $wp_query;

		if ( $wp_query->post_count ) {
			$template = filter_input( INPUT_POST, 'template', FILTER_SANITIZE_STRING );
			$template = apply_filters( 'infinite_pagination_template', $template );
			get_template_part( $template );
		}

		exit;
	}

	/**
	 * enqueue_scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		if ( is_home() || is_archive() ) {

			global $template;

			wp_localize_script( 'infinite-pagination', 'infinite_pagination', array(
				'post_type'      => get_query_var( 'post_type' ),
				'posts_per_page' => $this->posts_per_page,
				'_ajax_nonce'    => wp_create_nonce( self::AJAX_ACTION ),
				'template'       => basename( $template, '.php' ),
				'action'         => self::AJAX_ACTION,
			) );

			// call the hook to enqueue the infinite-pagination scripts
			do_action( 'add_infinite_pagination' );
		}
	}

	/**
	 * set_query_offset
	 *
	 * Manually determine page query offset (offset + current page (minus one) x posts per page)
	 *
	 * @param $query
	 */
	public function set_query_offset( &$query ) {
		if ( ! is_admin() && $query->is_paged && $query->query_vars['paged'] > 1 ) {
			$offset      = get_option( 'posts_per_page' );
			$page_offset = $offset + ( ( $query->query_vars['paged'] - 2 ) * $this->posts_per_page );
			$query->set( 'offset', $page_offset + 1 );
		}
	}
}

new InfinitePagination();
