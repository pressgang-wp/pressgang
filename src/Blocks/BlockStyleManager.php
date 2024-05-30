<?php

namespace PressGang\Blocks;

/**
 * Class BlockStyleManager
 *
 * Manages the styles for WordPress Blocks, extracting and formatting styles like margin and padding.
 * The class specifically handles spacing styles defined in the block's array structure and prepares them
 * for usage in rendering the block's view.
 *
 * @package PressGang\Blocks
 */
class BlockStyleManager {

	/**
	 * Retrieves and formats the style attributes for a WordPress Block.
	 *
	 * This method parses the block's array to extract style information, focusing particularly on spacing attributes
	 * such as margin and padding. It handles WordPress preset spacing as well, converting them into CSS variables.
	 *
	 * @param array $block The array representation of a Gutenberg block, containing its style and other attributes.
	 *
	 * @return array An array of CSS style strings ready to be used in the block's view.
	 */
	public static function get_styles( array $block ): array {

		$styles = [];

		// Loop through spacing attributes: margin and padding
		foreach ( [ 'margin', 'padding' ] as $spacing ) {
			foreach ( [ 'top', 'right', 'bottom', 'left' ] as $position ) {

				// Check if the spacing attribute is set for the current position
				if ( isset( $block['style']['spacing'][ $spacing ][ $position ] ) ) {
					$var = $block['style']['spacing'][ $spacing ][ $position ];

					// Handle WordPress preset spacing, converting them to CSS variables
					if ( str_starts_with( $var, 'var:' ) ) {
						$var = explode( '|', $var );
						$var = sprintf( 'var(--wp--preset--spacing--%s)', end( $var ) );
					}

					// Format the style string and add to the styles array
					$styles[] = sprintf( "%s-%s: %s;", $spacing, $position, $var );
				}
			}
		}

		return $styles;
	}
}

