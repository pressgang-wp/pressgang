<?php

namespace PressGang;

use Timber\Timber;

class Post extends \Timber\Post {
	protected array $related_posts = [];
	protected array $latest_posts = [];

	// Define CACHE_TIME as the value from wp-config.php or default to 1 day
	protected int $cache_time = 24 * 60 * 60;

	/**
	 * __construct
	 */
	protected function __construct() {
		// Set cache time when the class is instantiated
		$this->cache_time = defined( 'PRESSGANG_CACHE_TIME' ) ? PRESSGANG_CACHE_TIME : 24 * 60 * 60;

		parent::__construct();
	}

	/**
	 * get_related_posts
	 *
	 * @param int|null $posts_per_page Number of related posts to fetch.
	 *
	 * @return array Array of related posts.
	 */
	public function get_related_posts( ?int $posts_per_page = null ): array {
		$posts_per_page = $posts_per_page ?? \get_option( 'posts_per_page' );

		if ( empty( $this->related_posts ) ) {
			$key           = sprintf( "pressgang_related_posts_%d", $this->id );
			$related_posts = \wp_cache_get( $key, 'related_posts', true );

			if ( empty( $related_posts ) ) {
				$related_posts = $this->fetch_related_posts( $posts_per_page );
				\wp_cache_add( $key, $related_posts, 'related_posts', $this->cache_time );
			}

			$this->related_posts = $related_posts;
		}

		return $this->related_posts;
	}

	/**
	 * Fetches related posts based on the taxonomy terms and other criteria.
	 *
	 * This method constructs and executes the query to retrieve related posts
	 * for the current post, aiming to match posts by taxonomy terms.
	 * If there are insufficient related posts, it tries to fill the gap by
	 * relaxing the query constraints.
	 *
	 * @param int $posts_per_page
	 *
	 * @return array
	 */
	private function fetch_related_posts( int $posts_per_page ): array {
		$related_posts = [];
		$not_in        = [ $this->id ];

		// Build the initial query arguments
		$args = [
			'post_type'           => $this->post_type,
			'orderby'             => 'rand',
			'posts_per_page'      => $posts_per_page,
			'post__not_in'        => $not_in,
			'ignore_sticky_posts' => true,
			'tax_query'           => [ 'relation' => 'AND' ],
		];

		// Add taxonomy queries
		$this->add_taxonomy_queries( $args );

		// Fetch related posts
		$posts = Timber::get_posts( $args );
		foreach ( $posts as $post ) {
			$related_posts[ $post->ID ] = $post;
		}

		// Handle insufficient related posts
		if ( count( $related_posts ) < $posts_per_page ) {
			$this->fill_insufficient_related_posts( $related_posts, $args, $posts_per_page );
		}

		return $related_posts;
	}

	/**
	 * Adds taxonomy queries to the WP_Query arguments array.
	 *
	 * This method modifies the provided `$args` array to include taxonomy queries
	 * based on the current post's taxonomy terms. It adds queries that look for
	 * posts with matching terms in each taxonomy associated with the post type.
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	private function add_taxonomy_queries( array &$args ): void {
		$taxonomies = \get_object_taxonomies( $this->post_type, 'objects' );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = \wp_get_object_terms( $this->id, $taxonomy->name, [ 'fields' => 'ids' ] );
			if ( ! empty( $terms ) ) {
				$args['tax_query'][] = [
					'taxonomy'         => $taxonomy->name,
					'field'            => 'term_id',
					'terms'            => $terms,
					'operator'         => 'IN',
					'include_children' => false,
				];
			}
		}
	}

	/**
	 * Fills the related posts array when there are insufficient posts.
	 *
	 * This method is used to supplement the array of related posts if the initial query
	 * does not return enough posts. It modifies the query arguments to relax the conditions
	 * and fetch additional posts until the desired number is reached.
	 *
	 * @param array $related_posts
	 * @param array $args
	 * @param int $posts_per_page
	 *
	 * @return void
	 */
	private function fill_insufficient_related_posts( array &$related_posts, array $args, int $posts_per_page ): void {
		$not_in                        = array_merge( [ $this->id ], array_keys( $related_posts ) );
		$args['tax_query']['relation'] = 'OR';
		$args['post__not_in']          = $not_in;
		$args['posts_per_page']        = $posts_per_page - count( $related_posts );

		$posts = Timber::get_posts( $args );
		foreach ( $posts as $post ) {
			$related_posts[ $post->ID ] = $post;
		}

		// Further fallback if still insufficient posts
		if ( count( $related_posts ) < $posts_per_page ) {
			unset( $args['tax_query'] );
			$args['posts_per_page'] = $posts_per_page - count( $related_posts );
			$posts                  = Timber::get_posts( $args );

			foreach ( $posts as $post ) {
				$related_posts[ $post->ID ] = $post;
			}
		}
	}

	/**
	 * Retrieves the latest posts excluding the current post.
	 *
	 * This method fetches the latest posts of the same post type as the current post,
	 * excluding the current post itself. The result is cached to improve performance.
	 *
	 * @param int|null $posts_per_page The number of latest posts to retrieve. If null, it defaults to the 'posts_per_page' option.
	 *
	 * @return array The array of latest posts.
	 */
	public function get_latest_posts( ?int $posts_per_page = null ): array {
		// Use the default number of posts per page if not provided
		$posts_per_page = $posts_per_page ?? \get_option( 'posts_per_page' );

		// Check if latest posts have already been cached
		if ( empty( $this->latest_posts ) ) {
			$key = sprintf( "pressgang_latest_posts_%d", $this->id );

			// Try to get the cached posts
			$this->latest_posts = \wp_cache_get( $key, 'latest_posts', true );

			// If cache is empty, fetch the latest posts
			if ( empty( $this->latest_posts ) ) {
				$args = [
					'post_type'           => $this->post_type,
					'orderby'             => 'date',
					'order'               => 'DESC',
					'posts_per_page'      => $posts_per_page,
					'post__not_in'        => [ $this->id ],  // Exclude the current post
					'ignore_sticky_posts' => true, // Ignore sticky posts to maintain order
				];

				// Fetch the latest posts
				$this->latest_posts = Timber::get_posts( $args );

				// Cache the result for future requests
				\wp_cache_add( $key, $this->latest_posts, 'latest_posts', $this->cache_time );
			}
		}

		return $this->latest_posts;
	}
}
