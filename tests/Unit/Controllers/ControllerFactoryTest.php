<?php

// Fixture child-theme controllers. Extending the real framework controllers
// mirrors how a child theme overrides by convention.
namespace Acme\Theme\Controllers {
	class SearchController extends \PressGang\Controllers\SearchController {}
	class PostController extends \PressGang\Controllers\PostController {}
	class DummyController implements \PressGang\Controllers\ControllerInterface {
		public function __construct( public ?string $template = null ) {
		}

		public function render(): void {
		}
	}
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

		/** @test */
		public function make_requires_a_controller_class(): void {
			$controller = ControllerFactory::make( 'Acme\\Theme\\Controllers\\DummyController', 'dummy.twig' );

			$this->assertInstanceOf( 'Acme\\Theme\\Controllers\\DummyController', $controller );
			$this->assertSame( 'dummy.twig', $controller->template );

			$this->expectException( \InvalidArgumentException::class );

			ControllerFactory::make( \stdClass::class );
		}
		/** @test */
		public function explicit_template_reaches_the_controller_constructor(): void {
			$resolved = ControllerFactory::resolve_candidate_for(
				[ 'example', 'page' ],
				[ 'example' => [
					'controller' => \Acme\Theme\Controllers\DummyController::class,
					'template' => 'page/research-subpage.twig',
				] ],
				null
			);
			$this->assertSame( 'example', $resolved['candidate'] );
			$controller = ControllerFactory::make( $resolved['controller'], $resolved['twig'] );
			$this->assertSame( 'page/research-subpage.twig', $controller->template );
		}

		/** @test */
		public function class_and_array_entries_keep_candidate_discovery(): void {
			\Brain\Monkey\Functions\when( 'get_stylesheet_directory' )->justReturn( dirname( __DIR__, 2 ) . '/fixtures/controller-theme' );
			foreach ( [ \Acme\Theme\Controllers\DummyController::class,
				[ 'controller' => \Acme\Theme\Controllers\DummyController::class ],
				[ 'controller' => \Acme\Theme\Controllers\DummyController::class, 'template' => null ],
			] as $entry ) {
				$resolved = ControllerFactory::resolve_candidate_for( [ 'example' ], [ 'example' => $entry ], null );
				$this->assertSame( \Acme\Theme\Controllers\DummyController::class, $resolved['controller'] );
				$this->assertSame( 'example.twig', $resolved['twig'] );
			}
		}

		/** @test */
		public function specific_convention_precedes_a_less_specific_mapping(): void {
			\Brain\Monkey\Functions\when( 'get_stylesheet_directory' )->justReturn( dirname( __DIR__, 2 ) . '/fixtures/controller-theme' );
			$resolved = ControllerFactory::resolve_candidate_for(
				[ 'search', 'index' ],
				[ 'index' => [ 'controller' => PostController::class, 'template' => 'fallback.twig' ] ],
				self::CHILD
			);
			$this->assertSame( 'Acme\\Theme\\Controllers\\SearchController', $resolved['controller'] );
		}

	}
}
