<?php

Namespace PressGang;

use Timber\Timber;

/**
 * Class SinglePageController
 *
 * @package PressGang
 */
class SinglePageController extends PageController {

	protected $pages;

	/**
	 * __construct
	 *
	 * SinglePageController constructor.
	 *
	 * @param string $template
	 */
	public function __construct( $template = 'single-page.twig' ) {
		parent::__construct( $template );
	}

	/**
	 * get_context
	 *
	 */
	protected function get_context() {

		$this->context['pages'] = $this->get_pages();

		return $this->context;

	}

	/**
	 * get_children
	 *
	 */
	protected function get_pages() {

		if ( empty( $this->pages ) ) {

			$args = array(
				'post_parent' => $this->get_post()->ID,
				'post_type'   => 'page',
				'numberposts' => - 1,
				'post_status' => 'publish',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
			);

			$this->pages = array( $this->get_post() );

			$children = get_children( $args );

			foreach ( $children as &$child ) {

				$page = Timber::get_post( $child );

				$template = get_page_template_slug( $page->ID );
				$template = preg_replace( array( '/.*\//', '/\.php/i' ), array( '', '.twig' ), $template );

				$page->template = $template;
				$this->pages[]  = $page;
			}
		}

		return $this->pages;


	}
}
