<?php

namespace PressGang\Configuration;

/**
 * Registers custom Timber class maps from config/timber-class-map.php, allowing
 * post types and terms to be mapped to custom classes (e.g. 'post' => PressGang\Post).
 *
 * Why: enables post-type-specific behaviour (cached queries, custom methods) transparently.
 * Extend via: child theme config override or timber/{type}/classmap filter.
 *
 * @link https://timber.github.io/docs/v2/guides/class-maps/
 */
class TimberClassMap extends ConfigurationSingleton {

	/**
	 * @var array Array of classes to map.
	 */
	protected array $config;

	/**
	 * This method registers custom Timber class maps.
	 * Through various filters, you can tell Timber which class it should use
	 * for different WordPress elements, such as posts, terms, comments, menus,
	 * menu items, pages menus, and users.
	 *
	 * @link https://timber.github.io/docs/v2/guides/class-maps/
	 *
	 * @param array $config An array of WordPress elements (post types, terms, etc.)
	 *                      to their respective classes. The array should have keys
	 *                      representing the Timber classmap filter (e.g., 'post', 'term')
	 *                      and values being associative arrays where keys are slugs,
 *                          and values are the class names to be used.
	 *
	 * @return void
	 */
	public function initialize( array $config ): void {

		$this->config = $config;

		foreach ($this->config as $key => $mapping) {
			\add_filter("timber/{$key}/classmap", function ($classmap) use ($mapping) {
				return array_merge($classmap, $mapping);
			});
		}
	}
}
