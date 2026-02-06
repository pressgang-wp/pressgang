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
	public function add_to_context( array $context ): array {

		if ( function_exists( 'get_fields' ) && config( 'acf-options' ) ) {

			$fields = \wp_cache_get( 'theme_options' );

			if ( false === $fields ) {

				$fields = [];

				if ( $field_objects = \get_field_objects( 'option' ) ) {

					// Map the field objects to values and Timber objects where appropriate
					foreach ( $field_objects as $key => &$field ) {
						$fields[ $key ] = TimberMapper::map_field( $field );
					}

					\wp_cache_set( 'theme_options', $fields );
				}
			}

			$context['options'] = $fields;

		}

		return $context;
	}

}
