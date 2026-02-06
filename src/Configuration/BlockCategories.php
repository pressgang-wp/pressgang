<?php

namespace PressGang\Configuration;

/**
 * Registers custom block editor categories from config/block-categories.php.
 * Each entry is a slug => title pair that appears as a grouping in the block inserter.
 *
 * Why: keeps block categorisation declarative and consistent across parent/child themes.
 * Extend via: child theme config override or block_categories_all filter.
 *
 * @see https://developer.wordpress.org/reference/hooks/block_categories_all/
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
