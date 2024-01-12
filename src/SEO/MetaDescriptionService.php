<?php

namespace PressGang\SEO;

use Timber\TextHelper;
use Timber\Timber;

class MetaDescriptionService {
	private static string $meta_description = '';

	public static function get_meta_description() {
		if ( ! empty( self::$meta_description ) ) {
			return self::$meta_description;
		}

		if ( $object = \get_queried_object() ) {
			$key = self::generate_cache_key( $object );

			if ( ! self::$meta_description = \wp_cache_get( $key ) ) {
				self::$meta_description = self::get_default_description( $object );
				\wp_cache_set( $key, self::$meta_description );
			}
		} else {
			self::$meta_description = \get_bloginfo( 'description', 'raw' );
		}

		return self::sanitize_and_shorten_description( self::$meta_description );
	}

	private static function generate_cache_key( $object ): string {
		return sprintf( "meta_description_%s_%s", strtolower( get_class( $object ) ), $object->ID ?? $object->name );
	}

	private static function generate_description( $object ) {
		if ( \is_single() || \is_page() ) {
			return self::get_description_for_post( $object ) ?: self::get_default_description();
		} elseif ( \is_tax() ) {
			return self::get_description_for_taxonomy( $object ) ?: self::get_default_description();
		} elseif ( \is_post_type_archive() ) {
			return \get_the_archive_description() ?: self::get_default_description();
		}

		return self::get_default_description();
	}

	private static function get_description_for_post( $post ) {
		// Try Yoast SEO first
		$description = \get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

		if ( empty( $description ) ) {
			// Check for custom field
			$timberPost  = Timber::get_post( $post->ID );
			$description = \wptexturize( $timberPost->meta( 'meta_description' ) ) ?: $timberPost->post_excerpt ?: strip_shortcodes( $timberPost->post_content );
		}

		return $description;
	}

	private static function get_description_for_taxonomy( $term ) {
		// Try Yoast SEO for taxonomy
		$yoast_meta = \get_option( 'wpseo_taxonomy_meta' );
		if ( ! empty( $yoast_meta[ $term->taxonomy ][ $term->term_id ]['wpseo_desc'] ) ) {
			return $yoast_meta[ $term->taxonomy ][ $term->term_id ]['wpseo_desc'];
		}

		return \term_description( $term, \get_query_var( 'taxonomy' ) );
	}

	private static function get_default_description() {
		return \get_bloginfo( 'description', 'raw' );
	}

	private static function sanitize_and_shorten_description( $description ) {
		$description = \esc_attr( \wp_strip_all_tags( $description ) );

		if ( strlen( $description ) > 155 ) {
			$description = mb_substr( $description, 0, 155 );
			$description = TextHelper::trim_words( $description, str_word_count( $description ) - 1 );
		}

		return $description;
	}
}
