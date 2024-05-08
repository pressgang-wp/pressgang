<?php

namespace PressGang\SEO;

use Timber\TextHelper;
use Timber\Timber;

/**
 * Class MetaDescriptionService
 *
 * Provides services to generate and cache meta descriptions for various
 * WordPress content types. It supports posts, pages, taxonomies, and post type
 * archives, with support for Yoast SEO plugin data.
 *
 * @package PressGang\SEO
 */
class MetaDescriptionService {

	/**
	 * Set a character limit as recommended for SEO purposes
	 */
	const CHAR_LIMIT = 155;

	/**
	 * The Meta Description
	 *
	 * @var string
	 */
	private static string $meta_description = '';

	/**
	 * Retrieves the meta description for the current queried object.
	 *
	 * This method uses caching to avoid regenerating the meta description.
	 * It checks for descriptions from Yoast SEO, custom fields, or generates
	 * from content if necessary.
	 *
	 * @return string The sanitized and possibly shortened meta description.
	 */
	public static function get_meta_description(): string {
		if ( ! empty( self::$meta_description ) ) {
			return self::$meta_description;
		}

		if ( $object = \get_queried_object() ) {
			$key = self::generate_cache_key( $object );

			if ( ! self::$meta_description = \wp_cache_get( $key ) ) {
				self::$meta_description = self::generate_description( $object );
				\wp_cache_set( $key, self::$meta_description );
			}
		} else {
			self::$meta_description = self::get_default_description();
		}

		return \esc_attr( self::sanitize_and_shorten_description( self::$meta_description ) );
	}

	/**
	 * Generates a unique cache key based on the queried object.
	 *
	 * @param mixed $object The queried object (post, term, etc.).
	 *
	 * @return string The generated cache key.
	 */
	private static function generate_cache_key( $object ): string {
		return sprintf( "meta_description_%s_%s",
			strtolower( get_class( $object ) ), $object->ID ?? $object->name );
	}

	/**
	 * Generates a meta description based on the type of queried object.
	 *
	 * @param mixed $object The queried object (post, term, etc.).
	 *
	 * @return string The generated meta description.
	 */
	private static function generate_description( mixed $object ): string {
		if ( \is_single() || \is_page() ) {
			return self::get_description_for_post( $object ) ?: self::get_default_description();
		} elseif ( \is_tax() ) {
			return self::get_description_for_taxonomy( $object ) ?: self::get_default_description();
		} elseif ( \is_post_type_archive() ) {
			return \get_the_archive_description() ?: self::get_default_description();
		}

		return self::get_default_description();
	}

	/**
	 * Retrieves the meta description for a post or page.
	 *
	 * Tries to get description from Yoast SEO, custom fields, post excerpt, or
	 * post content.
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return string The meta description for the post.
	 */
	private static function get_description_for_post( \WP_Post $post ): string {
		// Try Yoast SEO first
		$description = \get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

		if ( empty( $description ) ) {
			// Check for custom field, then expert, then content
			$post        = Timber::get_post( $post->ID );
			$description = \wptexturize( $post->meta( 'meta_description' ) )
				?: $post->post_excerpt
					?: apply_filters( 'the_content', \strip_shortcodes( $post->post_content ) );
		}

		return $description;
	}

	/**
	 * Retrieves the meta description for a taxonomy term.
	 *
	 * Tries to get description from Yoast SEO or default term description.
	 *
	 * @param \WP_Term $term The term object.
	 *
	 * @return string The meta description for the taxonomy term.
	 */
	private static function get_description_for_taxonomy( \WP_Term $term ): string {
		// Try Yoast SEO for taxonomy
		$yoast_meta = \get_option( 'wpseo_taxonomy_meta' );
		if ( ! empty( $yoast_meta[ $term->taxonomy ][ $term->term_id ]['wpseo_desc'] ) ) {
			return $yoast_meta[ $term->taxonomy ][ $term->term_id ]['wpseo_desc'] ?? '';
		}

		return \term_description( $term, \get_query_var( 'taxonomy' ) );
	}

	/**
	 * Retrieves the default meta description for the site.
	 *
	 * @return string The site's default meta description.
	 */
	private static function get_default_description(): string {
		return \get_bloginfo( 'description', 'raw' );
	}

	/**
	 * Sanitizes and shortens the meta description to an appropriate length.
	 *
	 * Ensures the description is as close to 155 characters as possible without exceeding it and
	 * ends at a sentence boundary for better readability.
	 *
	 * @param string $description The meta description to be sanitized and
	 *     shortened.
	 *
	 * @return string The sanitized and shortened meta description.
	 */
	private static function sanitize_and_shorten_description( string $description ): string {
		$description = \wp_strip_all_tags( $description );

		if ( strlen( $description ) > self::CHAR_LIMIT ) {
			// Truncate at the nearest sentence boundary before 155 characters
			$truncated_description   = mb_substr( $description, 0, self::CHAR_LIMIT + 1 );
			$last_full_stop_position = strrpos( $truncated_description, '.' );
			if ( $last_full_stop_position !== false ) {
				$description = mb_substr( $description, 0, $last_full_stop_position + 1 );
			} else {
				// If no full stop, fall back to truncating at the nearest word boundary
				$description = mb_substr( $description, 0, self::CHAR_LIMIT );
				$description = TextHelper::trim_words( $description, str_word_count( $description ) - 1 );
			}
		}

		return $description;
	}

}
