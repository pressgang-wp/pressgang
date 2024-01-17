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
			case 'repeater':
			case 'flexible_content':
				return self::map_sub_fields( $field['value'] );

			case 'post_object':
				return Timber::get_post( $field['value'] );

			case 'term':
				return Timber::get_term( $field['value'] );

			case 'image':
				return Timber::get_image( $field['value'] );

			default:
				if ( is_array( $field['value'] ) ) {
					return self::map_sub_fields( $field['value'] );;
				}

				return $field['value'];
		}
	}

	/**
	 * Map ACF Sub Fields
	 * @see https://www.advancedcustomfields.com/resources/get_sub_field_object/
	 * @param array $sub_fields
	 *
	 * @return array
	 */
	public static function map_sub_fields( array $sub_fields ): array {
		foreach ( $sub_fields as &$sub_field ) {
			$sub_field['value'] = self::map_field( \get_sub_field_object( $sub_field['key'] ) );
		}

		return $sub_fields;
	}
}
