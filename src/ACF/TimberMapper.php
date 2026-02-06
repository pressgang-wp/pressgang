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
		return match ( $field['type'] ) {
			'post_object'                => Timber::get_post( $field['value'] ),
			'term'                       => Timber::get_term( $field['value'] ),
			'image'                      => Timber::get_image( $field['value'] ),
			'repeater', 'flexible_content' => $field['value'],
			default                      => $field['value'],
		};
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
		$sub_field_object = \get_sub_field_object( $key );
		if ( $sub_field_object ) {
			return self::map_field( $sub_field_object );
		}

		return $sub_field_object;
	}
}
