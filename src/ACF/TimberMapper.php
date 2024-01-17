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
				$items = [];
				foreach ( $field['value'] as $sub_field ) {
					$mapped_sub_field = [];
					foreach ( $sub_field as $key => $value ) {
						$mapped_sub_field[ $key ] = self::map_field( \get_sub_field_object( $key ) );
					}
					$items[] = $mapped_sub_field;
				}

				return $items;

			case 'post_object':
				return Timber::get_post( $field['value'] );

			case 'term':
				return Timber::get_term( $field['value'] );

			case 'image':
				return Timber::get_image( $field['value'] );

			default:
				if ( is_array( $field['value'] ) ) {
					$nested_items = [];
					foreach ( $field['value'] as $sub_key => $sub_value ) {
						$nested_items[ $sub_key ] = self::map_field( \get_sub_field_object( $sub_key )  );
					}

					return $nested_items;
				}

				return $field['value'];
		}
	}
}
