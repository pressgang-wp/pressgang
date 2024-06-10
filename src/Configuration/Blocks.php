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
	 *
	 * @https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	public function register_block_types(): void {

		foreach ( $this->config as $block_path => $args ) {

			// Check if the configuration is numeric (no args) or associative (with args)
			if ( is_int( $block_path ) ) {
				$block_path = $args;
				$args       = [];
			}

			// Determine the correct path to use
			$block_path = $this->resolve_block_path( $block_path );

			// Register the block type
			\register_block_type( $block_path, $args );
		}
	}

	/**
	 * Resolves the block path, checking child and parent themes, and caches the result.
	 *
	 * @param string $block_path The relative path to the block's registration file (block.json).
	 *
	 * @return string The resolved absolute path to the block's registration file.
	 */
	private function resolve_block_path( string $block_path ): string {
		$cache_key   = 'block_path_' . md5( $block_path );
		$cached_path = \wp_cache_get( $cache_key, 'blocks' );

		if ( $cached_path === false ) {
			$child_theme_path  = \get_stylesheet_directory() . $block_path;
			$parent_theme_path = \get_template_directory() . $block_path;

			$resolved_path = file_exists( $child_theme_path ) ? $child_theme_path : $parent_theme_path;

			// Cache the resolved path
			\wp_cache_set( $cache_key, $resolved_path, 'blocks' );
		} else {
			$resolved_path = $cached_path;
		}

		return $resolved_path;
	}
}
