<?php

namespace PressGang\Controllers;

use Doctrine\Inflector\InflectorFactory;
use Timber\Pagination;
use Timber\PostQuery;

/**
 * Controller for post listings — archives, categories, taxonomies, and search results.
 * Automatically infers the Twig template from the current query context when none is provided.
 */
class PostsController extends AbstractController {

	protected string $post_type;
	protected string $page_title;
	protected PostQuery $posts;
	protected Pagination $pagination;

	/**
	 * Infers the template from the current query context if none is provided.
	 *
	 * @see https://developer.wordpress.org/themes/basics/template-hierarchy/
	 *
	 * @param string|null $template
	 */
	public function __construct( string|null $template = null ) {

		global $wp_query;

		$this->post_type = $wp_query->query['post_type'] ?? \get_post_type();

		if ( ! $template ) {

			$template = 'archive.twig';

			// Try to guess the template
			if ( \is_category() ) {
				$template = 'category.twig';
			} else if ( \is_tax() ) {
				$taxonomy = \get_queried_object()->taxonomy;
				$taxonomy = str_replace( '_', '-', $taxonomy );
				$template = sprintf( "taxonomy%s.twig", $taxonomy === 'tag' ? '' : "-{$taxonomy}" );
			} else if ( \is_search() ) {
				$template = 'search.twig';
			} else {
				if ( $this->post_type && $this->post_type !== 'post' ) {
					$post_type_slug = str_replace( '_', '-', $this->post_type );
					$template       = sprintf( "archive-%s.twig", $post_type_slug );
				}
			}
		}

		parent::__construct( $template );
	}

	/**
	 * Returns the posts for the current WP_Query, lazily initialised.
	 *
	 * @see https://timber.github.io/docs/v2/reference/timber-postquery/
	 *
	 * @return PostQuery
	 */
	protected function get_posts(): PostQuery {
		if ( empty( $this->posts ) ) {
			global $wp_query;
			$this->posts = new PostQuery( $wp_query );
		}

		return $this->posts;
	}

	/**
	 * Adds posts, pagination, and page title to the context.
	 *
	 * @return array<string, mixed>
	 */
	#[\Override]
	protected function get_context(): array {
		$this->context['page_title'] = $this->get_page_title();

		$this->context['pagination'] = $this->get_pagination();
		$this->context['posts']      = $this->get_posts();

		if ( $this->post_type ) {
			$inflector = InflectorFactory::create()->build();
			$plural    = strtolower( $inflector->pluralize( $this->post_type ) );

			$this->context[ $plural ] = $this->get_posts();
		}

		return $this->context;
	}

	/**
	 * Returns the archive title, lazily initialised.
	 *
	 * @return string
	 */
	protected function get_page_title(): string {

		if ( empty( $this->page_title ) ) {
			$this->page_title = \get_the_archive_title();
		}

		return $this->page_title;
	}

	/**
	 * @see https://timber.github.io/docs/v2/guides/pagination/
	 *
	 * @return Pagination
	 */
	protected function get_pagination(): Pagination {

		if ( empty( $this->pagination ) ) {
			$this->pagination = $this->get_posts()->pagination();
		}

		return $this->pagination;
	}
}
