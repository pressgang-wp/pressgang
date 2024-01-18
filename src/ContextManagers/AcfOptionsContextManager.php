<?php

namespace PressGang\ContextManagers;

use PressGang\ACF\TimberMapper;

/**
 * Class AcfOptionsContextManager
 *
 * Manages the integration of Advanced Custom Fields (ACF) option fields into the Timber context.
 * This class retrieves ACF option fields and ensures that they are available within the theme's Twig templates.
 * It uses the ACFToTimberMapper to appropriately convert ACF fields to corresponding Timber objects.
 *
 * Implements the ContextManagerInterface to provide consistent handling of context data within the PressGang framework,
 * specifically for ACF option fields.
 *
 * @package PressGang\ContextManagers
 */
class AcfOptionsContextManager implements ContextManagerInterface {

	/**
	 * Adds ACF option fields to the Timber context.
	 *
	 * Retrieves ACF option fields using the get_field_objects() function and maps them to Timber objects using
	 * the ACFToTimberMapper. The mapped fields are then added to the Timber context, making them accessible
	 * within the theme's Twig templates. This allows for a seamless integration of ACF option fields into the theme.
	 *
	 * @param array $context The Timber context array that is passed to templates.
	 *
	 * @return array The modified context with added ACF options data.
	 */
	public function add_to_context( array $context ): array {

		if ( function_exists( 'get_fields' ) && config( 'acf-options' ) ) {

			$fields = \wp_cache_get( 'theme_options' );

			if ( false === $fields ) {
				$fields = \get_field_objects( 'option' );

				// Map the field objects to values and Timber objects where appropriate
				foreach ( $fields as $key => &$field ) {
					$field['value'] = TimberMapper::map_field( $field );
				}

				\wp_cache_set( 'theme_options', $fields );
			}

			$context['options'] = $fields;

		}

		return $context;
	}

}
