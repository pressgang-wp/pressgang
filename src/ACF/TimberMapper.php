<?php

namespace PressGang\ACF;

use Timber\Timber;

class TimberMapper {

	/**
	 * Maps ACF fields to corresponding Timber objects.
	 *
	 * @param array $field The ACF field array.
	 *
	 * @return mixed The corresponding Timber object or the original value if no mapping is defined.
	 */
	public static function map_field( array $field ): mixed {
		switch ( $field['type'] ) {
			case 'repeater':
			case 'flexible_content':
				$items = [];
				foreach ( $field['value'] as $subField ) {
					$mappedSubField = [];
					foreach ( $subField as $key => $value ) {
						$mappedSubField[ $key ] = self::map_field( $value );
					}
					$items[] = $mappedSubField;
				}

				return $items;

			case 'post_object':
				return Timber::get_post( $field['value'] );

			case 'term':
				return Timber::get_term( $field['value'] );

			case 'image':
				return Timber::get_image( $field['value'] );

			// Add additional mappings for other ACF field types if needed

			default:
				// Handle nested fields
				if ( is_array( $field['value'] ) ) {
					$nestedItems = [];
					foreach ( $field['value'] as $subKey => $subValue ) {
						$nestedItems[ $subKey ] = self::map_field( $subValue );
					}

					return $nestedItems;
				}

				return $field['value'];
		}
	}
}
