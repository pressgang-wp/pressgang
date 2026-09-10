<?php

namespace PressGang\Tests\Unit\Controllers {

	use Brain\Monkey\Functions;
	use PressGang\Controllers\PostsController;
	use PressGang\Tests\Unit\TestCase;

	/**
	 * Exposes template inference without booting the controller, whose
	 * constructor builds the Timber context.
	 */
	class InspectablePostsController extends PostsController {

		/**
		 * @param \WP_Post_Type|\WP_Term|null $queried_object
		 */
		public function __construct( string|array|null $template = null, private readonly mixed $queried_object = null ) {
			// Intentionally does not call parent::__construct().
		}

		/**
		 * @return string|array<int, string>
		 */
		public function inferred_template(): string|array {
			return $this->infer_template();
		}

		#[\Override]
		protected function get_queried_post_type(): ?string {
			return $this->queried_object instanceof \WP_Post_Type ? $this->queried_object->name : null;
		}

		#[\Override]
		protected function get_queried_term(): ?\WP_Term {
			return $this->queried_object instanceof \WP_Term ? $this->queried_object : null;
		}
	}

	/**
	 * Only a post-type archive names a single post type. Searches are
	 * `post_type=any` and taxonomy archives span every post type sharing the
	 * taxonomy, so neither may be described by one `archive-{type}` candidate.
	 */
	class PostsControllerTemplateTest extends TestCase {

		protected function setUp(): void {
			parent::setUp();

			foreach ( [ 'is_category', 'is_tag', 'is_tax', 'is_search' ] as $conditional ) {
				Functions\when( $conditional )->justReturn( false );
			}
		}

		private function post_type( string $name ): \WP_Post_Type {
			$post_type       = \Mockery::mock( \WP_Post_Type::class );
			$post_type->name = $name;

			return $post_type;
		}

		private function term( string $taxonomy ): \WP_Term {
			$term           = \Mockery::mock( \WP_Term::class );
			$term->taxonomy = $taxonomy;

			return $term;
		}

		/** @test */
		public function a_post_type_archive_gains_a_type_specific_candidate(): void {
			$this->assertSame(
				[ 'archive-event.twig', 'archive.twig' ],
				( new InspectablePostsController( null, $this->post_type( 'event' ) ) )->inferred_template()
			);
		}

		/** @test */
		public function underscores_in_post_type_names_become_hyphens(): void {
			$this->assertSame(
				'archive-news-item.twig',
				( new InspectablePostsController( null, $this->post_type( 'news_item' ) ) )->inferred_template()[0]
			);
		}

		/** @test */
		public function the_built_in_post_type_keeps_the_plain_archive(): void {
			$this->assertSame(
				'archive.twig',
				( new InspectablePostsController( null, $this->post_type( 'post' ) ) )->inferred_template()
			);
		}

		/** @test */
		public function a_listing_naming_no_post_type_falls_back_to_the_plain_archive(): void {
			// Date archives and the blog index query no single post type.
			$this->assertSame( 'archive.twig', ( new InspectablePostsController() )->inferred_template() );
		}

		/** @test */
		public function searches_never_gain_a_post_type_candidate(): void {
			// A search is post_type=any, so no archive-{type} template describes it.
			Functions\when( 'is_search' )->justReturn( true );

			$this->assertSame(
				[ 'search.twig', 'archive.twig' ],
				( new InspectablePostsController() )->inferred_template()
			);
		}

		/** @test */
		public function taxonomy_archives_route_by_taxonomy_not_by_post_type(): void {
			// research-theme spans six post types; the taxonomy is what identifies it.
			Functions\when( 'is_tax' )->justReturn( true );

			$this->assertSame(
				[ 'taxonomy-research-theme.twig', 'taxonomy.twig', 'archive.twig' ],
				( new InspectablePostsController( null, $this->term( 'research_theme' ) ) )->inferred_template()
			);
		}
	}
}
