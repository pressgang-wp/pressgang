<?php

// Fixture child-theme controllers. Extending the real framework controllers
// mirrors how a child theme overrides by convention.
namespace Acme\Theme\Controllers {
	class SearchController extends \PressGang\Controllers\SearchController {}
	class PostController extends \PressGang\Controllers\PostController {}
}

namespace PressGang\Tests\Unit\Controllers {

	use PressGang\Controllers\ControllerFactory;
	use PressGang\Controllers\PostController;
	use PressGang\Controllers\SearchController;
	use PressGang\Tests\Unit\TestCase;

	/**
	 * Tests child-theme-first controller resolution in ControllerFactory.
	 */
	class ControllerFactoryTest extends TestCase {

		private const CHILD = 'Acme\\Theme';

		/** @test */
		public function resolves_framework_controller_when_no_child_namespace(): void {
			$this->assertSame(
				SearchController::class,
				ControllerFactory::resolve_controller_class( 'search.php', null )
			);
		}

		/** @test */
		public function prefers_child_controller_over_framework(): void {
			$this->assertSame(
				'Acme\\Theme\\Controllers\\SearchController',
				ControllerFactory::resolve_controller_class( 'search.php', self::CHILD )
			);
		}

		/** @test */
		public function falls_back_to_post_controller_when_nothing_matches(): void {
			$this->assertSame(
				PostController::class,
				ControllerFactory::resolve_controller_class( 'no-such-template.php', null )
			);
		}

		/** @test */
		public function child_can_override_the_post_controller_fallback(): void {
			// No NoSuchTemplateController anywhere, so resolution lands on the
			// fallback — which the child theme has overridden.
			$this->assertSame(
				'Acme\\Theme\\Controllers\\PostController',
				ControllerFactory::resolve_controller_class( 'no-such-template.php', self::CHILD )
			);
		}

		/** @test */
		public function studly_cases_hyphenated_template_names(): void {
			// 'search.php' → SearchController proves the slug→StudlyCase mapping;
			// a hyphenated slug with no matching controller falls through to PostController.
			$this->assertSame(
				PostController::class,
				ControllerFactory::resolve_controller_class( 'single-product.php', null )
			);
		}
	}
}
