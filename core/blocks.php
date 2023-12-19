<?php

namespace PressGang;

/**
 * Class Blocks
 *
 * https://wordpress.org/gutenberg/handbook/designers-developers/developers/tutorials/block-tutorial/writing-your-first-block-type/
 *
 * @package PressGang
 */
class Blocks {

	protected $custom_categories = [];

	/**
	 * __construct
	 *
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'setup' ] );
		add_filter( 'block_categories_all', array( $this, 'add_custom_categories' ) );
	}

	/**
	 * setup
	 */
	public function setup() {
		$blocks = Config::get( 'blocks' );

		foreach ( $blocks as $path => $settings ) {

			// when category is an array use it to register custom categories
			// otherwise expect category to be the slug for a default gutenberg category
			if ( isset( $settings['category'] ) && is_array( $settings['category'] ) ) {

				$this->custom_categories[ $settings['category']['slug'] ] = $settings['category'];
				$settings['category']                                     = $settings['category']['slug'];

			}

			register_block_type( get_stylesheet_directory() . $path );
		}
	}

	/**
	 * add_custom_categories
	 *
	 * @param $categories
	 * @param $post
	 *
	 * @return array
	 */
	public function add_custom_categories( $categories ) {

		return array_values( array_merge( $categories, $this->custom_categories ) );

	}

}

new Blocks();
