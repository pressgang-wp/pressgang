<?php

namespace PressGang\ACF;

use Timber\Timber;

/**
 * Maps ACF field arrays to corresponding Timber objects (Post, Term, Image) by field
 * type. Used by AcfOptionsContextManager to convert raw ACF option values into
 * Timber-native objects before they reach templates.
 */
class TimberMapper {

	/**
	 * Maps an ACF field to a corresponding Timber object.
	 *
	 * @param array $field The ACF field array.
	 *
	 * @return mixed The corresponding Timber object, nested array of objects, or the original value.
	 */
	public static function map_field( array $field ): mixed {
		switch ( $field['type'] ) {

			case 'post_object':
				return Timber::get_post( $field['value'] );

			case 'term':
				return Timber::get_term( $field['value'] );

			case 'image':
				return Timber::get_image( $field['value'] );

			case 'repeater':
			case 'flexible_content':
				return $field['value'];

			default:
				return $field['value'];
		}
	}

	/**
	 * Map ACF Sub Fields
	 *
	 * @see https://www.advancedcustomfields.com/resources/get_sub_field_object/
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function map_sub_fields( string $key ): mixed {
		if ( $sub_field_object = \get_sub_field_object( $key ) ) {
			return self::map_field( $sub_field_object );
		}

		return $sub_field_object;

	}
}
