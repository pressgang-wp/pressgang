<?php

namespace PressGang\Blocks;

/**
 * Class BlockStyleManager
 *
 * Manages the styles for WordPress Blocks, extracting and formatting styles like spacing, colors, and typography.
 * The class handles style attributes defined in the block's array structure and prepares them
 * for usage in rendering the block's view.
 *
 * @package PressGang\Blocks
 */
class BlockStyleManager {

	/**
	 * Retrieves and formats the style attributes for a WordPress Block.
	 *
	 * This method parses the block's array to extract style information including spacing,
	 * colors, and typography. It handles WordPress preset values as well, converting them
	 * into CSS variables.
	 *
	 * @param array $block The array representation of a Gutenberg block, containing its style and other attributes.
	 *
	 * @return array An array of CSS style strings ready to be used in the block's view.
	 */
	public static function get_styles( array $block ): array {

		$styles = [];

		// Handle spacing (margin and padding)
		$styles = array_merge( $styles, self::get_spacing_styles( $block ) );

		// Handle color styles
		if ( isset( $block['style']['color'] ) ) {
			$styles = array_merge( $styles, self::get_color_styles( $block['style']['color'] ) );
		}

		// Handle typography styles
		if ( isset( $block['style']['typography'] ) ) {
			$styles = array_merge( $styles, self::get_typography_styles( $block['style']['typography'] ) );
		}

		return $styles;
	}

	/**
	 * Extracts spacing-related styles (margin and padding) from the block.
	 *
	 * @param array $block The block array.
	 *
	 * @return array CSS style strings for spacing.
	 */
	private static function get_spacing_styles( array $block ): array {
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
					$styles[] = sprintf( "%s-%s: %s", $spacing, $position, $var );
				}
			}
		}

		return $styles;
	}

	/**
	 * Extracts color-related styles from the block style array.
	 *
	 * @param array $color_styles The color section of $block['style']['color'].
	 *
	 * @return array CSS style strings for colors.
	 */
	private static function get_color_styles( array $color_styles ): array {
		$styles = [];

		// Handle text color
		if ( ! empty( $color_styles['text'] ) ) {
			$styles[] = sprintf( 'color: %s', self::process_preset_value( $color_styles['text'], 'color' ) );
		}

		// Handle background color
		if ( ! empty( $color_styles['background'] ) ) {
			$styles[] = sprintf( 'background-color: %s', self::process_preset_value( $color_styles['background'], 'color' ) );
		}

		// Handle gradient background
		if ( ! empty( $color_styles['gradient'] ) ) {
			$styles[] = sprintf( 'background: %s', self::process_preset_value( $color_styles['gradient'], 'gradient' ) );
		}

		return $styles;
	}

	/**
	 * Extracts typography-related styles from the block style array.
	 *
	 * @param array $typography_styles The typography section of $block['style']['typography'].
	 *
	 * @return array CSS style strings for typography.
	 */
	private static function get_typography_styles( array $typography_styles ): array {
		$styles = [];

		// Handle font size
		if ( ! empty( $typography_styles['fontSize'] ) ) {
			$styles[] = sprintf( 'font-size: %s', self::process_preset_value( $typography_styles['fontSize'], 'font-size' ) );
		}

		// Handle line height
		if ( ! empty( $typography_styles['lineHeight'] ) ) {
			$styles[] = sprintf( 'line-height: %s', $typography_styles['lineHeight'] );
		}

		// Handle font family
		if ( ! empty( $typography_styles['fontFamily'] ) ) {
			$styles[] = sprintf( 'font-family: %s', self::process_preset_value( $typography_styles['fontFamily'], 'font-family' ) );
		}

		// Handle font weight
		if ( ! empty( $typography_styles['fontWeight'] ) ) {
			$styles[] = sprintf( 'font-weight: %s', $typography_styles['fontWeight'] );
		}

		// Handle font style
		if ( ! empty( $typography_styles['fontStyle'] ) ) {
			$styles[] = sprintf( 'font-style: %s', $typography_styles['fontStyle'] );
		}

		// Handle text transform
		if ( ! empty( $typography_styles['textTransform'] ) ) {
			$styles[] = sprintf( 'text-transform: %s', $typography_styles['textTransform'] );
		}

		// Handle text decoration
		if ( ! empty( $typography_styles['textDecoration'] ) ) {
			$styles[] = sprintf( 'text-decoration: %s', $typography_styles['textDecoration'] );
		}

		// Handle letter spacing
		if ( ! empty( $typography_styles['letterSpacing'] ) ) {
			$styles[] = sprintf( 'letter-spacing: %s', $typography_styles['letterSpacing'] );
		}

		return $styles;
	}

	/**
	 * Processes preset values, converting WordPress preset format to CSS variables.
	 *
	 * Handles format: var:preset|{type}|{slug} -> var(--wp--preset--{type}--{slug})
	 * Also handles direct values by returning them as-is.
	 *
	 * @param string $value The preset value or direct CSS value.
	 * @param string $type The preset type (e.g., 'color', 'gradient', 'font-size').
	 *
	 * @return string The processed value.
	 */
	private static function process_preset_value( string $value, string $type ): string {
		// Check for WordPress preset format: var:preset|type|slug
		if ( str_starts_with( $value, 'var:preset|' ) ) {
			$parts = explode( '|', $value );
			if ( count( $parts ) === 3 ) {
				// Convert to CSS variable format
				return sprintf( 'var(--wp--preset--%s--%s)', $parts[1], $parts[2] );
			}
		}

		// Return direct values as-is (hex colors, px values, etc.)
		return $value;
	}
}
