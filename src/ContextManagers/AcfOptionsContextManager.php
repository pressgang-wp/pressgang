<?php

namespace PressGang\ContextManagers;

use PressGang\ACF\TimberMapper;

/**
 * Adds ACF options-page fields to the global context under 'options'.
 *
 * Raw ACF field objects are cached (via wp_cache) so the expensive
 * get_field_objects() lookup runs once per request/cache lifetime; values are then
 * converted to Timber objects (Post, Term, Image) via TimberMapper on every request.
 * The cache is invalidated on 'acf/save_post' so option edits appear immediately.
 *
 * Why: options data is read across many templates and rarely changes, so caching the
 * raw fields keeps every request cheap without serialising Timber objects.
 * Extend via: override the protected seams (is_acf_active, map_field) in a child theme
 * subclass, or the 'acf/save_post' hook for custom invalidation.
 */
class AcfOptionsContextManager implements ContextManagerInterface {

	private const CACHE_KEY = 'theme_option_field_objects';
	private const CACHE_GROUP = 'pressgang';

	public function __construct() {
		\add_action( 'acf/save_post', [ $this, 'invalidate_cache' ] );
	}

	/**
	 * @param array<string, mixed> $context
	 *
	 * @return array<string, mixed>
	 */
	#[\Override]
	public function add_to_context( array $context ): array {

		if ( $this->is_acf_active() ) {
			$context['options'] = $this->map_field_objects( $this->get_field_objects() );
		}

		return $context;
	}

	/**
	 * Clears the cached raw field objects. Bound to 'acf/save_post' so edits take effect.
	 *
	 * @return void
	 */
	public function invalidate_cache(): void {
		\wp_cache_delete( self::CACHE_KEY, self::CACHE_GROUP );
	}

	/**
	 * Checks whether ACF is active and acf-options config is present.
	 *
	 * @return bool
	 */
	protected function is_acf_active(): bool {
		return function_exists( 'get_fields' ) && config( 'acf-options' );
	}

	/**
	 * Returns the raw ACF option field objects, reading through the object cache.
	 *
	 * Invariant: raw (unmapped) field objects are cached; mapping to Timber objects
	 * happens per request so cached data stays serialisation-safe.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	protected function get_field_objects(): array {
		$field_objects = \wp_cache_get( self::CACHE_KEY, self::CACHE_GROUP );

		if ( false === $field_objects ) {
			$field_objects = $this->load_field_objects();

			\wp_cache_set( self::CACHE_KEY, $field_objects, self::CACHE_GROUP );
		}

		return $field_objects;
	}

	/**
	 * Loads the raw ACF option field objects from ACF (testability seam for get_field_objects()).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	protected function load_field_objects(): array {
		$field_objects = \get_field_objects( 'option' );

		return is_array( $field_objects ) ? $field_objects : [];
	}

	/**
	 * Maps each raw ACF field object to its Timber-aware value, keyed by field name.
	 *
	 * @param array<string, mixed> $field_objects
	 *
	 * @return array<string, mixed>
	 */
	protected function map_field_objects( array $field_objects ): array {
		$options = [];

		foreach ( $field_objects as $key => $field ) {
			if ( is_array( $field ) ) {
				$options[ $key ] = $this->map_field( $field );
			}
		}

		return $options;
	}

	/**
	 * Maps a single ACF field to a Timber object where appropriate
	 * (testability seam for TimberMapper::map_field()).
	 *
	 * @param array<string, mixed> $field
	 *
	 * @return mixed
	 */
	protected function map_field( array $field ): mixed {
		return TimberMapper::map_field( $field );
	}

}
