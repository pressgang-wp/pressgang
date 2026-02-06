<?php

namespace PressGang\Configuration;

use function Symfony\Component\String\u;

/**
 * Registers a custom Block Editor color palette from config/color-palette.php.
 * Accepts either simple slug => hex pairs (labels are auto-generated) or full
 * arrays with slug, name, and color keys.
 *
 * Why: keeps color palette registration declarative with auto-generated labels.
 * Extend via: child theme config override.
 *
 * @see https://developer.wordpress.org/block-editor/developers/themes/theme-support/#block-color-palettes
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

		foreach ( $this->config as $key => $entry ) {

			if ( ! empty( $entry ) ) {
				if ( ! is_array( $entry ) ) {
					$readableName = u( $key )->replace( '-', ' ' )->title( true );

					$entry = [
						'slug'  => $key,
						'name'  => $readableName->toString(),
						'color' => $entry,
					];
				}

				if ( ! isset( $entry['slug'] ) ) {
					$entry['slug'] = $key;
				}

				$palette[] = $entry;
			}
		}

		// Register the color palette with the Block Editor
		add_theme_support( 'editor-color-palette', $palette );

		// TODO deprecate to support.php
		// disable custom Colors
		// add_theme_support( 'disable-custom-colors' );
	}
}
