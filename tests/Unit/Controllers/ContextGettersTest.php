<?php

// Fixture controller with a declared context manifest. Instantiated without
// its constructor in tests (the real constructor initialises Timber).
namespace Acme\Theme\Controllers {

	class ManifestController extends \PressGang\Controllers\AbstractController {

		protected array $context_getters = [
			'news',
			'featured' => 'get_hero_featured',
		];

		protected function get_news(): array {
			return [ 'a', 'b' ];
		}

		protected function get_hero_featured(): string {
			return 'hero';
		}

		public function exposed_apply( array $context ): array {
			return $this->apply_context_getters( $context );
		}
	}
}

namespace PressGang\Tests\Unit\Controllers {

	use PressGang\Tests\Unit\TestCase;

	/**
	 * Tests the declarative context manifest on AbstractController.
	 */
	class ContextGettersTest extends TestCase {

		private function make_controller(): \Acme\Theme\Controllers\ManifestController {
			return ( new \ReflectionClass( \Acme\Theme\Controllers\ManifestController::class ) )
				->newInstanceWithoutConstructor();
		}

		/** @test */
		public function plain_entries_call_the_conventional_getter(): void {
			$context = $this->make_controller()->exposed_apply( [] );

			$this->assertSame( [ 'a', 'b' ], $context['news'] );
		}

		/** @test */
		public function keyed_entries_override_the_getter_name(): void {
			$context = $this->make_controller()->exposed_apply( [] );

			$this->assertSame( 'hero', $context['featured'] );
		}

		/** @test */
		public function manifest_values_win_over_existing_context_keys(): void {
			$context = $this->make_controller()->exposed_apply( [ 'news' => 'stale' ] );

			$this->assertSame( [ 'a', 'b' ], $context['news'] );
		}

		/** @test */
		public function unrelated_context_keys_pass_through(): void {
			$context = $this->make_controller()->exposed_apply( [ 'site' => 'kept' ] );

			$this->assertSame( 'kept', $context['site'] );
		}
	}
}
