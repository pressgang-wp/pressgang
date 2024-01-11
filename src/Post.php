<?php

namespace PressGang;

use Doctrine\Inflector\InflectorFactory;
use Timber\Timber;

class Post extends \Timber\Post {
	protected array $related_posts = [];
	protected array $latest_posts = [];

	const CACHE_TIME = 24 * 60 * 60;

	/**
	 * get_related_posts
	 *
	 * @param null $posts_per_page
	 *
	 * @return array
	 */
	public function get_related_posts( $posts_per_page = null ): array {

		$posts_per_page = $posts_per_page ?? \get_option( 'posts_per_page' );

		if ( empty( $this->related_posts ) ) {

			$key = sprintf( "pressgang_related_posts_%d", $this->id );

			$this->related_posts = \wp_cache_get( $key, 'related_posts', true );

			if ( empty( $this->related_posts ) ) {

				$this->related_posts = [];

				// search post with AND terms

				$not_in = [ $this->id ];

				$args = [
					'post_type'           => $this->post_type,
					'orderby'             => 'rand',
					'posts_per_page'      => $posts_per_page,
					'post__not_in'        => $not_in,
					'ignore_sticky_posts' => true,
					'tax_query'           => [
						'relation' => 'AND',
					],
				];

				$taxonomies = \get_object_taxonomies( $this->post_type, 'objects' );

				foreach ( $taxonomies as $taxonomy ) {

					if ( $terms = \wp_get_object_terms( $this->id, $taxonomy->name, [ 'fields' => 'ids' ] ) ) {
						$args['tax_query'][] = [
							'taxonomy'         => $taxonomy->name,
							'field'            => 'term_id',
							'terms'            => $terms,
							'operator'         => 'IN',
							'include_children' => false,
						];
					}
				}

				$posts = Timber::get_posts( $args );

				foreach ( $posts as $post ) {
					$this->related_posts[ $post->ID ] = $post;
				}

				if ( count( $this->related_posts ) < $posts_per_page ) {

					// merge related posts with OR terms

					$not_in = array_merge( $not_in, array_keys( $this->related_posts ) );

					$args['tax_query']['relation'] = 'OR';
					$args['post__not_in']          = $not_in;
					$args['posts_per_page']        = $posts_per_page - count( $this->related_posts );

					$posts = Timber::get_posts( $args );

					foreach ( $posts as $post ) {
						$this->related_posts[ $post->ID ] = $post;
					}

					// fill the rest with related posts by post_type
					if ( count( $this->related_posts ) < $posts_per_page ) {
						$not_in = array_merge( $not_in, array_keys( $this->related_posts ) );

						unset( $args['tax_query'] );
						$args['post__not_in']   = $not_in;
						$args['posts_per_page'] = $posts_per_page - count( $this->related_posts );

						$posts = Timber::get_posts( $args );

						foreach ( $posts as $post ) {
							$this->related_posts[ $post->ID ] = $post;
						}
					}
				}

				\wp_cache_add( $key, $this->related_posts, 'related_posts', 0 );
			}
		}

		return $this->related_posts;
	}

	/**
	 * get_latest_posts
	 *
	 * @param $post
	 * @param $tags
	 *
	 * @return array
	 */
	public function get_latest_posts( $posts_per_page = null ): array {

		$posts_per_page = $posts_per_page ?? \get_option( 'posts_per_page' );

		if ( empty( $this->latest_posts ) ) {

			$key = sprintf( "pressgang_latest_posts_%d", $this->id );

			$this->latest_posts = \wp_cache_get( $key, 'latest_posts', true );

			if ( empty( $this->latest_posts ) ) {

				$this->latest_posts = [];

				$args = [
					'post_type'      => $this->post_type,
					'orderby'        => 'date',
					'posts_per_page' => $posts_per_page,
					'post__not_in'   => [ $this->id ],
				];

				$this->latest_posts = Timber::get_posts( $args );

				\wp_cache_add( $key, $this->latest_posts, 'latest_posts', self::CACHE_TIME );
			}
		}

		return $this->latest_posts;
	}
}
