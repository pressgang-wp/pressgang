<?php

namespace PressGang\Configuration;

use function Symfony\Component\String\u;

/**
 * Class ColorPalette
 *
 * Registers a custom color palette for the Block Editor in WordPress.
 *
 * This class allows defining a set of colors in the theme that can be used within the Block Editor,
 * enhancing the design consistency across the site.
 *
 * @see https://developer.wordpress.org/block-editor/developers/themes/theme-support/#block-color-palettes
 * @package PressGang
 */
class ColorPalette extends ConfigurationSingleton {

	/**
	 * Initializes the ColorPalette class with configuration data.
	 *
	 * Sets up the configuration for the color palette and adds an action hook
	 * to register the color palette after the theme setup.
	 *
	 * @param array $config The configuration array for the color palette.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		add_action( 'after_setup_theme', [ $this, 'add_color_palette' ], 50 );
	}

	/**
	 * Theme Setup Function
	 *
	 * Registers the color palette with the WordPress Block Editor.
	 *
	 * If 'slug' is not set for a color, it automatically generates one from its config key.
	 *
	 * If the config is a simple key-value pair, where the key is the slug (kebab-case) and the value is the color code
	 * the 'name' will be auto-generated from the slug.
	 *
	 * @hooked action 'after_setup_theme'
	 */
	public function add_color_palette(): void {

		$palette = [];

		foreach ( $this->config as $key => $palette ) {

			if ( ! empty( $palette ) ) {
				if ( ! is_array( $palette ) ) {
					$readableName = u( $key )->replace( '-', ' ' )->title( true );

					$palette = [
						'slug'  => $key,
						'name'  => $readableName->toString(),
						'color' => $palette
					];
				}

				if ( ! isset( $palette['slug'] ) ) {
					$palette['slug'] = $key;
				}
			}
		}

		// Register the color palette with the Block Editor
		add_theme_support( 'editor-color-palette', $palette );

		// TODO deprecate to support.php
		// disable custom Colors
		// add_theme_support( 'disable-custom-colors' );
	}
}
