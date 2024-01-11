<?php

namespace PressGang\Blocks;

/**
 * Class BlockClassManager
 *
 * Manages the CSS class names for WordPress Blocks, formatting and compiling them based on the block's attributes.
 * This class facilitates the creation of a dynamic list of CSS classes based on the properties of a block,
 * such as alignment, text color, and background color.
 *
 * @package PressGang\Blocks
 */
class BlockClassManager {

	/**
	 * Retrieves and compiles CSS class names for a WordPress Block.
	 *
	 * This method inspects the block's array for specific attributes and formats them into CSS class names.
	 * It handles standard block properties like className, backgroundColor, textColor, and align, formatting them
	 * into appropriate CSS class names to be applied to the block's HTML structure.
	 *
	 * @param array $block The array representation of a Gutenberg block, containing its properties and attributes.
	 *
	 * @return array An array of CSS class names derived from the block's properties.
	 */
	public static function get_css_classes( $block ) {

		$classes = [];

		// Add the block's custom class name if set
		if ( isset( $block['className'] ) ) {
			$classes[] = $block['className'];
		}

		// Handle background color class
		if ( isset( $block['backgroundColor'] ) ) {
			$classes[] = $block['backgroundColor'];
			$classes[] = sprintf( "has-%s-background-color", $block['backgroundColor'] );
		}

		// Handle text color class
		if ( isset( $block['textColor'] ) ) {
			$classes[] = sprintf( "has-%s-color", $block['textColor'] );
		}

		// Add alignment class if set
		if ( ! empty( $block['align'] ) ) {
			$classes[] = sprintf( "align-%s", $block['align'] );
		}

		return $classes;
	}
}
