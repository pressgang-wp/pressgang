<?php

namespace PressGang;

use Doctrine\Inflector\InflectorFactory;
use Timber\PostQuery;

/**
 * Class PostsController
 *
 * @package PressGang
 */
class PostsController extends BaseController {

	protected $posts;
	protected $post_type;
	protected $page_title;
	protected $pagination;

	/**
	 * __construct
	 *
	 * PostsController constructor
	 *
	 * @param string $template
	 */
	public function __construct( $template = null, $post_type = null ) {

		global $wp_query;

		if ( ! $post_type && isset( $wp_query->query['post_type'] ) ) {
			$post_type = $wp_query->query['post_type'];
		}

		if ( ! $post_type && $temp = get_post_type() ) {
			$post_type = $temp;
		}

		$this->post_type = $post_type;

		if ( ! $template ) {
			// try to guess the view for custom post types

			if ( is_category() ) {
				$template = 'category.twig';
			} else if ( is_tax() ) {

				$obj      = get_queried_object();
				$template = sprintf( "taxonomy-%s.twig", $obj->taxonomy );

				// check template exits
				$caller = LocationManager::get_calling_script_dir( 1 );
				$loader = new Loader( $caller );
				$file   = $loader->choose_template( $template );

				if ( ! $file ) {
					$template = 'taxonomy.twig';
				}

			} else if ( is_search() ) {
				$template = 'search.twig';
			} else {
				$template = sprintf( "archive%s.twig", $this->post_type === 'post' ? '' : "-{$this->post_type}" );
			}

		}

		parent::__construct( array( $template, 'archive.twig' ) );
	}

	/**
	 * get_posts
	 *
	 * @return mixed
	 */
	protected function get_posts() {
		if ( empty( $this->posts ) ) {
			global $wp_query;
			$this->posts = new PostQuery( $wp_query );
		}

		return $this->posts;
	}

	/**
	 * get_context
	 *
	 * @return mixed
	 */
	protected function get_context() {
		$this->context['page_title'] = $this->get_page_title();

		$this->context['posts']      = $this->get_posts();
		$this->context['pagination'] = $this->get_pagination();

		if ( $this->post_type ) {
			$inflector = InflectorFactory::create()->build();
			$plural    = strtolower( $inflector->pluralize( $this->post_type ) );

			$this->context[ $plural ] = $this->get_posts();
		}

		return $this->context;
	}

	/**
	 * get_page_title
	 *
	 * @return string|void
	 */
	protected function get_page_title() {

		if ( empty( $this->page_title ) ) {
			$this->page_title = get_the_archive_title();
		}

		return $this->page_title;
	}

	/**
	 * get_pagination
	 *
	 * @return mixed
	 */
	protected function get_pagination() {

		if ( empty( $this->pagination ) ) {
			$this->pagination = $this->get_posts()->pagination();
		}

		return $this->pagination;
	}
}
