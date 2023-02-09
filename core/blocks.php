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

	/**
	 * __construct
	 *
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'setup' ] );
	}

	/**
	 * setup
	 */
	public function setup() {
		$blocks = Config::get( 'blocks' );

		foreach ( $blocks as $key => &$path ) {
			register_block_type( get_stylesheet_directory() . $path );
		}
	}

}

new Blocks();
