<?php

namespace PressGang\Controllers;

use Doctrine\Inflector\InflectorFactory;
use Timber\Pagination;
use Timber\PostQuery;

/**
 * Class PostsController
 *
 * Controller for handling post listings in a WordPress theme.
 * Extends the AbstractController to add specific functionalities for post archives, categories, taxonomies, and custom post types.
 *
 * @package PressGang
 */
class PostsController extends AbstractController {

	protected string $post_type;
	protected string $page_title;
	protected PostQuery $posts;
	protected Pagination $pagination;

	/**
	 * PostsController constructor.
	 *
	 * Initializes the controller for handling posts with a specified template and post type.
	 * Dynamically determines the appropriate template based on the current query context.
	 *
	 * @see https://developer.wordpress.org/themes/basics/template-hierarchy/
	 *
	 * @param string|null $template The template file to use for rendering the posts. Defaults to null, which triggers automatic template determination.
	 * @param string|null $post_type The specific post type to handle. Defaults to null, which uses the current queried post type.
	 */
	public function __construct( $template = null, string $post_type = null ) {

		global $wp_query;

		// Set post_type from the parameter, or fall back to the global query or current post type
		$this->post_type = $post_type ?: ( $wp_query->query['post_type'] ?? \get_post_type() );

		if ( ! $template ) {
			// Try to guess the template
			if ( \is_category() ) {
				$template = 'category.twig';
			} else if ( \is_tax() ) {
				$taxonomy = \get_queried_object()->taxonomy;
				$template = sprintf( "taxonomy%s.twig", $taxonomy === 'tag' ? '' : "-{$taxonomy}" );
			} else if ( \is_search() ) {
				$template = 'search.twig';
			} else {
				$template = sprintf( "archive%s.twig", $this->post_type === 'post' ? '' : "-{$this->post_type}" );
			}
		}

		parent::__construct( $template );
	}

	/**
	 * Get the posts for the current query.
	 *
	 * Retrieves the posts based on the current WordPress query and caches them for later use.
	 *
	 * @see https://timber.github.io/docs/v2/reference/timber-postquery/
	 * @return PostQuery The Timber PostQuery object representing the current set of posts.
	 */
	protected function get_posts(): PostQuery {
		if ( empty( $this->posts ) ) {
			global $wp_query;
			$this->posts = new PostQuery( $wp_query );
		}

		return $this->posts;
	}

	/**
	 * Get the context for the template rendering.
	 *
	 * Extends the base get_context method from AbstractController, adding posts, page title, and pagination data
	 * to the context for use in the template.
	 *
	 * @return array The context array with additional data for the posts.
	 */
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
	 * Get the page title for the current archive.
	 *
	 * Retrieves and caches the title for the current post archive, category, taxonomy, or custom post type.
	 *
	 * @return string The page title for the current archive view.
	 */
	protected function get_page_title(): string {

		if ( empty( $this->page_title ) ) {
			$this->page_title = \get_the_archive_title();
		}

		return $this->page_title;
	}

	/**
	 * Get the pagination data for the current posts query.
	 *
	 * Retrieves and caches the pagination data based on the current post query.
	 *
	 * @see https://timber.github.io/docs/v2/guides/pagination/
	 * @return Pagination Pagination data for the current set of posts.
	 */
	protected function get_pagination(): Pagination {

		if ( empty( $this->pagination ) ) {
			$this->pagination = $this->get_posts()->pagination();
		}

		return $this->pagination;
	}
}
