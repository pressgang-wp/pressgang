<?php

namespace PressGang\Configuration;

/**
 * Class Blocks
 *
 * Handles the registration of WordPress `Blocks`.
 * It reads the block configuration and registers each block type.
 *
 * @link https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
 * @package PressGang
 */
class Blocks extends ConfigurationSingleton {

	/**
	 * Initializes the Blocks class with configuration data.
	 *
	 * Sets up the configuration and adds action hooks for Gutenberg block registration.
	 *
	 * @param array $config The configuration array for Gutenberg blocks.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'register_block_types' ] );
	}

	/**
	 * Registers block types and handles custom block categories.
	 *
	 * Iterates through the configuration array and registers each block type.
	 * Also manages the registration of custom block categories.
	 */
	public function register_block_types(): void {

		foreach ( $this->config as $block_path ) {

			// Check for block definition in child theme
			$child_theme_path  = \get_stylesheet_directory() . $block_path;
			$parent_theme_path = \get_template_directory() . $block_path;

			// Determine the correct path to use
			$block_path = file_exists( $child_theme_path ) ? $child_theme_path : $parent_theme_path;

			\register_block_type( $block_path );
		}
	}

}
