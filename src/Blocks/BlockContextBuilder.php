<?php

namespace PressGang\Blocks;

use PressGang\ACF\ACFToTimberMapper;
use \Timber\Timber;

/**
 * BlockContextBuilder class responsible for building the context for rendering blocks.
 *
 * This class provides functionality to build and compile the context needed for rendering
 * WordPress Blocks. It includes relevant data such as post information, block-specific
 * attributes, styles, and classes, making it available to the block's Twig template.
 */
class BlockContextBuilder {

	/**
	 * Builds the context for a given Gutenberg block.
	 *
	 * This method constructs an array of contextual data required for rendering a block.
	 * It gathers the necessary information, including the current post, block-specific details,
	 * CSS classes, and inline styles. Additionally, it retrieves any custom fields associated with
	 * the block, adding them to the context.
	 *
	 * @see https://www.advancedcustomfields.com/resources/get_field_objects/
	 *
	 * @param array $block The WordPress Block array containing details such as block ID, styles,
	 *                     and other attributes.
	 *
	 * @return array An associative array of context data to be used in rendering the block's template.
	 */
	public static function build_context( $block ) {
		$context         = [];
		$context['post'] = Timber::get_post();
		$context['id']   = $block['id'];

		$context['classes'] = BlockClassManager::get_css_classes( $block );
		$context['styles']  = BlockStyleManager::get_styles( $block );

		$context['block'] = $block;

		// Retrieve all ACF custom fields for the Block
		if ( $fields = \get_field_objects() ) {
			foreach ( $fields as $field ) {
				// Add each field's value to the context array, using the field's name as the key
				// This makes all ACF custom fields accessible in the block's Twig template
				$context[ $field['name'] ] = ACFToTimberMapper::map_acf_to_timber( $field['value'] );
			}
		}

		return $context;
	}
}
