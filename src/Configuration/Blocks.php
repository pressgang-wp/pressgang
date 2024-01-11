<?php

namespace PressGang\Configuration;

/**
 * Class Blocks
 *
 * Handles the registration of WordPress `Blocks` and custom block categories.
 * It reads the block configuration and registers each block type, also managing custom categories.
 *
 * @link https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
 * @package PressGang
 */
class Blocks extends ConfigurationSingleton {

	/**
	 * @var array
	 */
	protected array $custom_categories = [];

	/**
	 * Initializes the Blocks class with configuration data.
	 *
	 * Sets up the configuration and adds action hooks for Gutenberg block registration
	 * and custom block category addition.
	 *
	 * @param array $config The configuration array for Gutenberg blocks.
	 */
	public function initialize( array $config ) {
		$this->config = $config;
		\add_action( 'init', [ $this, 'register_block_types' ] );
		\add_filter( 'block_categories_all', [ $this, 'add_custom_categories' ] );
	}

	/**
	 * Registers block types and handles custom block categories.
	 *
	 * Iterates through the configuration array and registers each block type.
	 * Also manages the registration of custom block categories.
	 */
	public function register_block_types(): void {

		foreach ( $this->config as $path => $settings ) {

			// Check for block definition in child theme
			$child_theme_path = \get_stylesheet_directory() . $path;
			$parent_theme_path = \get_template_directory() . $path;

			// Determine the correct path to use
			$block_path = file_exists($child_theme_path) ? $child_theme_path : $parent_theme_path;

			// When the `category` parameter is an array, use it to register custom categories
			// otherwise, expect the category to be the slug for a default block category.
			if (isset($settings['category']) && is_array($settings['category'])) {
				$this->custom_categories[$settings['category']['slug']] = $settings['category'];
				$settings['category'] = $settings['category']['slug'];
			}

			\register_block_type($block_path);
		}
	}

	/**
	 * Adds custom block categories to the block editor.
	 *
	 * @param array $categories Existing block categories.
	 *
	 * @return array Modified array of block categories including custom ones.
	 */
	public function add_custom_categories( array $categories ): array {

		return array_values( array_merge( $categories, $this->custom_categories ) );

	}

}
