<?php

namespace PressGang\ACF;

use Timber\Timber;

/**
 * Class TimberMapper
 *
 * Maps ACF fields to corresponding Timber objects.
 *
 * @package PressGang\ACF
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
				foreach ( $field['value'] as &$row ) {
					foreach ( $row as $key => &$sub_field ) {
						var_dump( $sub_field );
						// $sub_field['value'] = self::map_field( $sub_field );
					}
				}

				return $field['value'];

			default:

				var_dump( $field['type'] );

				return $field['value'];

				if ( isset( $field['key'] ) && is_string( $field['key'] ) ) {
					return self::map_sub_fields( $field['key'] );
				}

				return $field['value'];
		}
	}

	/**
	 * Map ACF Sub Fields
	 * 
	 * @see https://www.advancedcustomfields.com/resources/get_sub_field_object/
	 *
	 * @param array $key
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
