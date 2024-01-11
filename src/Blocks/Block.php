<?php

namespace PressGang\Blocks;

use \Timber\Timber;

/**
 * Block class responsible for rendering WordPress Blocks.
 *
 * This class handles the rendering of blocks within the PressGang framework
 * using the Timber library.
 *
 * It is designed to abstract and simplify the process of rendering a block's associated Twig template.
 */
class Block {

	/**
	 * Renders a WordPress Block using Timber.
	 *
	 * This method takes a block array, extracts its slug, and then uses Timber
	 * to render the corresponding Twig template. The context for rendering is built
	 * using the BlockContextBuilder class, ensuring all necessary data is passed to
	 * the Twig template.
	 *
	 * @param array $block The block array. This array contains all the
	 *                     necessary information about the block, including its name
	 *                     and other attributes.
	 */
	public static function render( $block ) {
		$slug    = substr( $block['name'], strpos( $block['name'], '/' ) + 1 );
		$context = BlockContextBuilder::build_context( $block );

		Timber::render( "blocks/{$slug}.twig", $context );
	}
}
