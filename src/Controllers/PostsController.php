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
	protected ?string $page_title = null;
	protected ?PostQuery $posts = null;
	protected ?Pagination $pagination = null;

	/**
	 * Infers the template from the current query context if none is provided.
	 *
	 * @see https://developer.wordpress.org/themes/basics/template-hierarchy/
	 *
	 * @param string|array<int, string>|null $template
	 */
	public function __construct( string|array|null $template = null ) {

		global $wp_query;

		$this->post_type = $wp_query->query['post_type'] ?? \get_post_type();

		parent::__construct( $template ?: $this->infer_template() );
	}

	/**
	 * Builds the Twig template fallback chain for the current query context.
	 *
	 * Mirrors the WordPress template hierarchy: the most specific template
	 * first, falling back towards `archive.twig`. Timber renders the first
	 * template in the chain that exists, so a theme without `category.twig`
	 * still renders categories via `archive.twig` instead of a blank page.
	 *
	 * @return string|array<int, string>
	 */
	protected function infer_template(): string|array {

		if ( \is_category() ) {
			return [ 'category.twig', 'archive.twig' ];
		}

		if ( \is_tag() ) {
			return [ 'tag.twig', 'archive.twig' ];
		}

		if ( \is_tax() ) {
			$taxonomy = str_replace( '_', '-', \get_queried_object()->taxonomy );

			return [ "taxonomy-{$taxonomy}.twig", 'taxonomy.twig', 'archive.twig' ];
		}

		if ( \is_search() ) {
			return [ 'search.twig', 'archive.twig' ];
		}

		if ( $this->post_type && $this->post_type !== 'post' ) {
			$post_type_slug = str_replace( '_', '-', $this->post_type );

			return [ "archive-{$post_type_slug}.twig", 'archive.twig' ];
		}

		return 'archive.twig';
	}

	/**
	 * Returns the posts for the current WP_Query, lazily initialised.
	 *
	 * @see https://timber.github.io/docs/v2/reference/timber-postquery/
	 *
	 * @return PostQuery
	 */
	protected function get_posts(): PostQuery {
		if ( $this->posts === null ) {
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

		if ( $this->page_title === null ) {
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

		if ( $this->pagination === null ) {
			$this->pagination = $this->get_posts()->pagination();
		}

		return $this->pagination;
	}
}
