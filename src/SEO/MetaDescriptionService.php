<?php

namespace PressGang\SEO;

use Timber\TextHelper;
use Timber\Timber;

class MetaDescriptionService {
	private static $meta_description;

	public static function getMetaDescription() {
		if ( self::$meta_description !== null ) {
			return self::$meta_description;
		}

		if ( $object = \get_queried_object() ) {
			$key = self::generateCacheKey( $object );

			if ( ! self::$meta_description = \wp_cache_get( $key ) ) {
				self::$meta_description = self::generateDescription( $object );
				\wp_cache_set( $key, self::$meta_description );
			}
		} else {
			self::$meta_description = \get_bloginfo( 'description', 'raw' );
		}

		return self::sanitizeAndShortenDescription( self::$meta_description );
	}

	private static function generateCacheKey( $object ) {
		return sprintf( "meta_description_%s_%s", strtolower( get_class( $object ) ), $object->ID ?? $object->name );
	}

	private static function generateDescription( $object ) {
		if ( \is_single() || \is_page() ) {
			return self::getDescriptionForPost( $object ) ?: self::getDefaultDescription();
		} elseif ( \is_tax() ) {
			return self::getDescriptionForTaxonomy( $object ) ?: self::getDefaultDescription();
		} elseif ( \is_post_type_archive() ) {
			return \get_the_archive_description() ?: self::getDefaultDescription();
		}

		return self::getDefaultDescription();
	}

	private static function getDescriptionForPost( $post ) {
		// Try Yoast SEO first
		$description = \get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

		if ( empty( $description ) ) {
			// Check for custom field
			$timberPost  = Timber::get_post( $post->ID );
			$description = \wptexturize( $timberPost->meta( 'meta_description' ) ) ?: $timberPost->post_excerpt ?: strip_shortcodes( $timberPost->post_content );
		}

		return $description;
	}

	private static function getDescriptionForTaxonomy( $term ) {
		// Try Yoast SEO for taxonomy
		$yoast_meta = \get_option( 'wpseo_taxonomy_meta' );
		if ( ! empty( $yoast_meta[ $term->taxonomy ][ $term->term_id ]['wpseo_desc'] ) ) {
			return $yoast_meta[ $term->taxonomy ][ $term->term_id ]['wpseo_desc'];
		}

		return \term_description( $term, \get_query_var( 'taxonomy' ) );
	}

	private static function getDefaultDescription() {
		return \get_bloginfo( 'description', 'raw' );
	}

	private static function sanitizeAndShortenDescription( $description ) {
		$description = \esc_attr( \wp_strip_all_tags( $description ) );

		if ( strlen( $description ) > 155 ) {
			$description = mb_substr( $description, 0, 155 );
			$description = TextHelper::trim_words( $description, str_word_count( $description ) - 1 );
		}

		return $description;
	}
}
