<?php

namespace PressGang\Configuration;

/**
 * Class Block Categories
 *
 * Handles the registration of custom WordPress Block Categories.
 *
 * @package PressGang
 */
class BlockCategories extends ConfigurationSingleton {

	/**
	 * @var array
	 */
	protected array $config = [];

	/**
	 * Sets up the configuration and adds action hooks for block category registration.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/block_categories_all/
	 *
	 * @param array $config The configuration array for Gutenberg blocks.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_filter( 'block_categories_all', [ $this, 'add_custom_categories' ] );
	}

	/**
	 * Adds custom block categories to the block editor.
	 *
	 * @param array $categories Existing block categories.
	 *
	 * @hooked block_categories_all
	 *
	 * @return array Modified array of block categories including custom ones.
	 */
	public function add_custom_categories( array $categories ): array {

		$custom_categories = [];

		foreach ( $this->config as $key => $val ) {
			$custom_categories[] = [
				'slug' => $key,
				'title' => $val,
			];
		}

		return array_values( array_merge( $categories, $custom_categories ) );

	}

}
