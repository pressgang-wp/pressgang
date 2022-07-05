<?php

namespace PressGang;

/**
 * Class Block Patterns
 *
 * @see - https://developer.wordpress.org/block-editor/reference-guides/block-api/block-patterns/
 *
 * @package PressGang
 */
class BlockPatterns {

	/**
	 * __construct
	 *
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup' ) );
	}

	/**
	 * setup
	 */
	public function setup() {

		$block_patterns = Config::get( 'block-patterns' );

		foreach ( $block_patterns as $key => &$args ) {
			register_block_pattern( $key, $args );
		}
	}

}

new BlockPatterns();
