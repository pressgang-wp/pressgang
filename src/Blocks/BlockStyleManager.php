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
	 * @param array<string, mixed> $block The array representation of a Gutenberg block, containing its style and other attributes.
	 *
	 * @return array<int, string> An array of CSS style strings ready to be used in the block's view.
	 */
	public static function get_styles( array $block ): array {

		$styles = [];

		$style = $block['style'] ?? [];
		if ( ! is_array( $style ) ) {
			return $styles;
		}

		// Handle spacing (margin and padding)
		$styles = array_merge( $styles, self::get_spacing_styles( $style ) );

		// Handle color styles
		if ( isset( $style['color'] ) && is_array( $style['color'] ) ) {
			$styles = array_merge( $styles, self::get_color_styles( $style['color'] ) );
		}

		// Handle typography styles
		if ( isset( $style['typography'] ) && is_array( $style['typography'] ) ) {
			$styles = array_merge( $styles, self::get_typography_styles( $style['typography'] ) );
		}

		return $styles;
	}

	/**
	 * Extracts spacing-related styles (margin and padding) from the block's style array.
	 *
	 * @param array<string, mixed> $style The block's style array ($block['style']).
	 *
	 * @return array<int, string> CSS style strings for spacing.
	 */
	private static function get_spacing_styles( array $style ): array {
		$styles = [];

		$spacing_styles = $style['spacing'] ?? [];

		if ( ! is_array( $spacing_styles ) ) {
			return $styles;
		}

		// Loop through spacing attributes: margin and padding
		foreach ( [ 'margin', 'padding' ] as $spacing ) {
			foreach ( [ 'top', 'right', 'bottom', 'left' ] as $position ) {

				// Check if the spacing attribute is set for the current position
				if ( isset( $spacing_styles[ $spacing ] ) && is_array( $spacing_styles[ $spacing ] ) && isset( $spacing_styles[ $spacing ][ $position ] ) ) {
					$var = $spacing_styles[ $spacing ][ $position ];

					if ( ! is_string( $var ) || $var === '' ) {
						continue;
					}

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
	 * @param array<string, mixed> $color_styles The color section of $block['style']['color'].
	 *
	 * @return array<int, string> CSS style strings for colors.
	 */
	private static function get_color_styles( array $color_styles ): array {
		$styles = [];

		if ( ( $text = self::style_string( $color_styles, 'text' ) ) !== null ) {
			$styles[] = sprintf( 'color: %s', self::process_preset_value( $text, 'color' ) );
		}

		if ( ( $background = self::style_string( $color_styles, 'background' ) ) !== null ) {
			$styles[] = sprintf( 'background-color: %s', self::process_preset_value( $background, 'color' ) );
		}

		if ( ( $gradient = self::style_string( $color_styles, 'gradient' ) ) !== null ) {
			$styles[] = sprintf( 'background: %s', self::process_preset_value( $gradient, 'gradient' ) );
		}

		return $styles;
	}

	/**
	 * Extracts typography-related styles from the block style array.
	 *
	 * @param array<string, mixed> $typography_styles The typography section of $block['style']['typography'].
	 *
	 * @return array<int, string> CSS style strings for typography.
	 */
	private static function get_typography_styles( array $typography_styles ): array {
		$styles = [];

		if ( ( $font_size = self::style_string( $typography_styles, 'fontSize' ) ) !== null ) {
			$styles[] = sprintf( 'font-size: %s', self::process_preset_value( $font_size, 'font-size' ) );
		}

		if ( ( $line_height = self::style_string( $typography_styles, 'lineHeight' ) ) !== null ) {
			$styles[] = sprintf( 'line-height: %s', $line_height );
		}

		if ( ( $font_family = self::style_string( $typography_styles, 'fontFamily' ) ) !== null ) {
			$styles[] = sprintf( 'font-family: %s', self::process_preset_value( $font_family, 'font-family' ) );
		}

		if ( ( $font_weight = self::style_string( $typography_styles, 'fontWeight' ) ) !== null ) {
			$styles[] = sprintf( 'font-weight: %s', $font_weight );
		}

		if ( ( $font_style = self::style_string( $typography_styles, 'fontStyle' ) ) !== null ) {
			$styles[] = sprintf( 'font-style: %s', $font_style );
		}

		if ( ( $text_transform = self::style_string( $typography_styles, 'textTransform' ) ) !== null ) {
			$styles[] = sprintf( 'text-transform: %s', $text_transform );
		}

		if ( ( $text_decoration = self::style_string( $typography_styles, 'textDecoration' ) ) !== null ) {
			$styles[] = sprintf( 'text-decoration: %s', $text_decoration );
		}

		if ( ( $letter_spacing = self::style_string( $typography_styles, 'letterSpacing' ) ) !== null ) {
			$styles[] = sprintf( 'letter-spacing: %s', $letter_spacing );
		}

		return $styles;
	}

	/**
	 * Narrows $styles[$key] to a non-empty string, or null.
	 *
	 * @param array<string, mixed> $styles
	 */
	private static function style_string( array $styles, string $key ): ?string {
		$value = $styles[ $key ] ?? null;

		return ! empty( $value ) && is_string( $value ) ? $value : null;
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
