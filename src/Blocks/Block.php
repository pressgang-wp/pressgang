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
	 * @see https://www.advancedcustomfields.com/resources/acf-blocks-key-concepts/#block-variables-or-parameters-for-callbacks-in-php
	 *
	 * @param array $block The block array. This array contains all the
	 *                     necessary information about the block, including its name
	 *                     and other attributes.
	 *
	 * @param string $content The block inner HTML (empty).
	 * @param boolean $is_preview True during backend preview render, i.e., when rendering inside the block editor’s content, or rendered inside the block editor when adding a new block, showing a preview when hovering over the new block. This variable is only set to true when is_admin() and current screen is_block_editor() both return true.
	 * @param integer $post_id The Post ID of the current context. This will be the page/post a block is saved against, or if the block is used in a template, synced pattern or query loop block, it will be the post_id of the currently displayed item.
	 * @param $wp_block
	 * @param array $context The context provided to the block by the post or its parent block.
	 */
	public static function render( array $block, string $content, bool $is_preview, int $post_id, $wp_block, array $context ): void {
		$slug    = substr( $block['name'], strpos( $block['name'], '/' ) + 1 );
		$context = static::get_context( $block );

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
	protected static function get_context( mixed $block ): array {
		return BlockContextBuilder::build_context( $block );
	}
}
