<?php

namespace PressGang\ACF;

use Timber\Post;
use Timber\Timber;

/**
 * Maps ACF field arrays to corresponding Timber objects (Post, Term, Image) by field
 * type. Used by AcfOptionsContextManager to convert raw ACF option values into
 * Timber-native objects before they reach templates.
 *
 * Also provides to_timber_posts() for converting raw relationship/post-object
 * values anywhere ACF data is fetched directly (controllers, models, snippets).
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
			'relationship'               => self::to_timber_posts( $field['value'] ),
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

	/**
	 * Converts an ACF relationship/post-object value (WP_Post objects or IDs)
	 * to Timber posts. Accepts the raw field value, so empty/false values are
	 * fine.
	 *
	 * Note: for top-level fields, Timber's `timber/meta/transform_value`
	 * filter (globally or per `meta()` call) can transform values without this
	 * helper. The transformer keys off the queried field's type, however, so
	 * values read from flexible-content or group sub-fields still come through
	 * raw — this helper covers those cases. See CLAUDE.md "Timber-First Data
	 * Access" for the full rationale on preferring explicit conversion over
	 * the global filter.
	 *
	 * @param mixed $value Raw ACF field value.
	 *
	 * @return array<int, Post>
	 */
	public static function to_timber_posts( mixed $value ): array {

		$posts = array_map( Timber::get_post( ... ), is_array( $value ) ? $value : [] );

		return array_values( array_filter( $posts ) );
	}
}
