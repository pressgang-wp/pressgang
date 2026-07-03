<?php

// Fixture child-theme controller for convention-based candidate resolution.
namespace Acme\Theme\Controllers {
	if ( ! class_exists( 'Acme\\Theme\\Controllers\\ArchiveEventController' ) ) {
		class ArchiveEventController extends \PressGang\Controllers\PostsController {}
	}
	if ( ! class_exists( 'Acme\\Theme\\Controllers\\GigsController' ) ) {
		class GigsController extends \PressGang\Controllers\PostsController {}
	}
	if ( ! class_exists( 'Acme\\Theme\\Controllers\\VenueController' ) ) {
		class VenueController extends \PressGang\Controllers\PostController {}
	}
}

namespace PressGang\Tests\Unit\Controllers {

	use Brain\Monkey\Functions;
	use PressGang\Controllers\ControllerFactory;
	use PressGang\Tests\Unit\TestCase;

	/**
	 * Tests candidate-based controller resolution (config map + convention).
	 */
	class CandidateResolutionTest extends TestCase {

		private const CHILD = 'Acme\\Theme';

		protected function setUp(): void {
			parent::setUp();

			// No child views directory in unit tests — candidate twig resolves to null.
			Functions\when( 'get_stylesheet_directory' )->justReturn( '/nonexistent' );
		}

		/** @test */
		public function explicit_map_entry_wins_for_most_specific_candidate(): void {
			$resolved = ControllerFactory::resolve_candidate_for(
				[ 'taxonomy-hit-group', 'taxonomy-hit_group', 'taxonomy' ],
				[ 'taxonomy-hit-group' => \PressGang\Controllers\TaxonomyController::class ],
				self::CHILD
			);

			$this->assertSame( \PressGang\Controllers\TaxonomyController::class, $resolved['controller'] );
			$this->assertSame( 'taxonomy-hit-group', $resolved['candidate'] );
		}

		/** @test */
		public function child_convention_resolves_when_map_is_empty(): void {
			$resolved = ControllerFactory::resolve_candidate_for(
				[ 'archive-event', 'archive' ],
				[],
				self::CHILD
			);

			$this->assertSame( 'Acme\\Theme\\Controllers\\ArchiveEventController', $resolved['controller'] );
		}

		/** @test */
		public function parent_framework_controllers_are_not_matched_by_convention(): void {
			// PressGang\Controllers\SearchController exists, but convention
			// matching is child-only — parent templates already route to it.
			$this->assertNull(
				ControllerFactory::resolve_candidate_for( [ 'search' ], [], null )
			);
		}

		/** @test */
		public function unresolvable_candidates_return_null(): void {
			$this->assertNull(
				ControllerFactory::resolve_candidate_for(
					[ 'single-thing', 'single', 'singular' ],
					[],
					self::CHILD
				)
			);
		}

		/** @test */
		public function archive_candidate_infers_pluralised_controller(): void {
			$resolved = ControllerFactory::resolve_candidate_for( [ 'archive-gig', 'archive' ], [], self::CHILD );

			$this->assertSame( 'Acme\\Theme\\Controllers\\GigsController', $resolved['controller'] );
		}

		/** @test */
		public function single_candidate_infers_subject_controller(): void {
			$resolved = ControllerFactory::resolve_candidate_for( [ 'single-venue', 'single' ], [], self::CHILD );

			$this->assertSame( 'Acme\\Theme\\Controllers\\VenueController', $resolved['controller'] );
		}

		/** @test */
		public function taxonomy_candidate_infers_subject_controller(): void {
			// taxonomy-venue resolves to VenueController via the taxonomy- prefix rule.
			$resolved = ControllerFactory::resolve_candidate_for( [ 'taxonomy-venue', 'taxonomy' ], [], self::CHILD );

			$this->assertSame( 'Acme\\Theme\\Controllers\\VenueController', $resolved['controller'] );
		}

		/** @test */
		public function registered_page_template_slug_falls_back_to_page_controller(): void {
			$resolved = ControllerFactory::resolve_candidate_for(
				[ 'contact-page', 'page-contact', 'page' ],
				[],
				self::CHILD,
				[ 'contact-page', 'grid-page' ]
			);

			$this->assertSame( \PressGang\Controllers\PageController::class, $resolved['controller'] );
			$this->assertSame( 'contact-page', $resolved['candidate'] );
		}

		/** @test */
		public function convention_controller_beats_page_template_fallback(): void {
			// VenueController exists; candidate 'venue' registered as page template.
			$resolved = ControllerFactory::resolve_candidate_for(
				[ 'venue' ],
				[],
				self::CHILD,
				[ 'venue' ]
			);

			$this->assertSame( 'Acme\\Theme\\Controllers\\VenueController', $resolved['controller'] );
		}

		/** @test */
		public function map_entries_with_missing_classes_are_skipped(): void {
			$resolved = ControllerFactory::resolve_candidate_for(
				[ 'archive-event' ],
				[ 'archive-event' => 'No\\Such\\Controller' ],
				self::CHILD
			);

			$this->assertSame( 'Acme\\Theme\\Controllers\\ArchiveEventController', $resolved['controller'] );
		}
	}
}
