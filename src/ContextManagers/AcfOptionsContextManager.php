<?php

namespace PressGang\ContextManagers;

use PressGang\ACF\TimberMapper;

/**
 * Adds ACF options-page fields to the global context as 'options'. Field values are
 * converted to Timber objects (Post, Term, Image) via TimberMapper and cached with wp_cache.
 */
class AcfOptionsContextManager implements ContextManagerInterface {

	/**
	 * @param array<string, mixed> $context
	 *
	 * @return array<string, mixed>
	 */
	#[\Override]
	public function add_to_context( array $context ): array {

		if ( $this->is_acf_active() ) {

			$fields = \wp_cache_get( 'theme_options' );

			if ( false === $fields ) {

				$fields = [];

				if ( $field_objects = \get_field_objects( 'option' ) ) {

					// Map the field objects to values and Timber objects where appropriate
					foreach ( $field_objects as $key => &$field ) {
						$fields[ $key ] = $this->map_field( $field );
					}

					\wp_cache_set( 'theme_options', $fields );
				}
			}

			$context['options'] = $fields;

		}

		return $context;
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
	 * Maps an ACF field to a Timber object where appropriate.
	 *
	 * @param array $field
	 *
	 * @return mixed
	 */
	protected function map_field( array $field ): mixed {
		return TimberMapper::map_field( $field );
	}

}
