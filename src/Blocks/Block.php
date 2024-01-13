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
	public static function render( array $block ): void {
		$slug    = substr( $block['name'], strpos( $block['name'], '/' ) + 1 );
		$context = self::get_context( $block );

		Timber::render( "blocks/{$slug}.twig", $context );
	}

	/**
	 * Provides a get_context method allowing subclasses to override it for custom context generation.
	 *
	 * This method is used to build the context for a given block.
	 *
	 * @param mixed $block The block for which the context is to be built.
	 *                     This could be any type depending on how the context is structured.
	 *
	 * @return array An array representing the context for the specified block.
	 */
	protected static function get_context( $block ): array {
		return BlockContextBuilder::build_context( $block );
	}
}
