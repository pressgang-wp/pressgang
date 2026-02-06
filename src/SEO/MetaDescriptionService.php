<?php

namespace PressGang\SEO;

use Timber\TextHelper;

/**
 * Generates and caches SEO meta descriptions for posts, pages, taxonomies, and
 * archives. Falls back through Yoast SEO, custom fields, excerpt, then content,
 * and truncates to 155 characters at a sentence boundary.
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

		$object = \get_queried_object();

		if ( ! $object ) {
			self::$meta_description = self::get_default_description();
		} else {
			$key = self::generate_cache_key( $object );

			if ( ! self::$meta_description = \wp_cache_get( $key ) ) {
				self::$meta_description = self::generate_and_cache_description( $object, $key );
			}
		}

		return self::$meta_description;
	}

	/**
	 * Generates a unique cache key based on the queried object.
	 *
	 * @param mixed $object The queried object (post, term, etc.).
	 *
	 * @return string The generated cache key.
	 */
	private static function generate_cache_key( $object ): string {
		return sprintf( "meta_description_%s_%s", strtolower( get_class( $object ) ), $object->ID ?? $object->name );
	}

	/**
	 * Generates a meta description based on the type of queried object and caches it.
	 *
	 * @param mixed $object The queried object (post, term, etc.).
	 * @param string $key The cache key.
	 *
	 * @return string The generated meta description.
	 */
	private static function generate_and_cache_description( $object, string $key ): string {
		$description = self::generate_description( $object );
		$description = self::sanitize_and_shorten_description( $description );
		\wp_cache_set( $key, $description );

		return $description;
	}

	/**
	 * Generates a meta description based on the type of queried object.
	 *
	 * @param mixed $object The queried object (post, term, etc.).
	 *
	 * @return string The generated meta description.
	 */
	private static function generate_description( $object ): string {

		if ( \is_front_page() ) {
			return self::get_default_description();
		}

		if ( \is_single() || \is_page() ) {
			return self::get_description_for_post( $object ) ?: self::get_default_description();
		}

		if ( \is_tax() ) {
			return self::get_description_for_taxonomy( $object ) ?: self::get_default_description();
		}

		if ( \is_post_type_archive() ) {
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
		$description = \get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
		if ( ! empty( $description ) ) {
			return $description;
		}

		$description = \get_post_meta( $post->ID, 'meta_description' )
			?: \get_the_excerpt( $post->ID )
				?: \apply_filters( 'the_content', \get_the_content( null, false, $post->ID ) );

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
		$yoast_meta = \get_option( 'wpseo_taxonomy_meta' );

		return $yoast_meta[ $term->taxonomy ][ $term->term_id ]['wpseo_desc'] ?? \term_description( $term, \get_query_var( 'taxonomy' ) );
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
	 * @param string $description The meta description to be sanitized and shortened.
	 *
	 * @return string The sanitized and shortened meta description.
	 */
	private static function sanitize_and_shorten_description( string $description ): string {
		$description = \wp_strip_all_tags( $description );
		if ( strlen( $description ) <= self::CHAR_LIMIT ) {
			return $description;
		}

		$truncated_description   = mb_substr( $description, 0, self::CHAR_LIMIT + 1 );
		$last_full_stop_position = strrpos( $truncated_description, '.' );
		if ( $last_full_stop_position !== false ) {
			return mb_substr( $description, 0, $last_full_stop_position + 1 );
		}

		$description = mb_substr( $description, 0, self::CHAR_LIMIT );

		return TextHelper::trim_words( $description, str_word_count( $description ) - 1 );
	}
}
