<?php

namespace PressGang\Tests\Unit\ContextManagers;

use Brain\Monkey\Functions;
use PressGang\ContextManagers\AcfOptionsContextManager;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests AcfOptionsContextManager: ACF availability check, wp_cache hit/miss,
 * field object mapping, and cache population.
 *
 * Uses anonymous subclasses to override is_acf_active() and map_field()
 * so the test doesn't depend on ACF, TimberMapper, or the config() helper.
 */
class AcfOptionsContextManagerTest extends TestCase {

	/**
	 * Creates a manager with ACF inactive.
	 *
	 * @return AcfOptionsContextManager
	 */
	private function makeInactiveManager(): AcfOptionsContextManager {
		return new class extends AcfOptionsContextManager {
			protected function is_acf_active(): bool {
				return false;
			}

			protected function map_field( array $field ): mixed {
				return $field['value'] ?? null;
			}
		};
	}

	/**
	 * Creates a manager with ACF active and map_field() returning the value directly.
	 *
	 * @return AcfOptionsContextManager
	 */
	private function makeActiveManager(): AcfOptionsContextManager {
		return new class extends AcfOptionsContextManager {
			protected function is_acf_active(): bool {
				return true;
			}

			protected function map_field( array $field ): mixed {
				return $field['value'] ?? null;
			}
		};
	}

	/** @test */
	public function returns_context_unchanged_when_acf_not_active(): void {
		$original = [ 'site' => 'test' ];

		$manager = $this->makeInactiveManager();
		$context = $manager->add_to_context( $original );

		$this->assertSame( $original, $context );
		$this->assertArrayNotHasKey( 'options', $context );
	}

	/** @test */
	public function returns_cached_options_on_cache_hit(): void {
		$cached = [ 'site_logo' => 'logo.png', 'phone' => '555-1234' ];
		Functions\expect( 'wp_cache_get' )->with( 'theme_options' )->andReturn( $cached );

		$manager = $this->makeActiveManager();
		$context = $manager->add_to_context( [] );

		$this->assertSame( $cached, $context['options'] );
	}

	/** @test */
	public function loads_and_caches_fields_on_cache_miss(): void {
		Functions\expect( 'wp_cache_get' )->with( 'theme_options' )->andReturn( false );

		$fieldObjects = [
			'site_logo' => [ 'type' => 'image', 'value' => 'logo.png' ],
			'phone'     => [ 'type' => 'text', 'value' => '555-1234' ],
		];

		Functions\expect( 'get_field_objects' )->with( 'option' )->andReturn( $fieldObjects );

		Functions\expect( 'wp_cache_set' )
			->once()
			->with( 'theme_options', \Mockery::on( function ( $fields ) {
				return $fields['site_logo'] === 'logo.png'
					&& $fields['phone'] === '555-1234';
			} ) );

		$manager = $this->makeActiveManager();
		$context = $manager->add_to_context( [] );

		$this->assertSame( 'logo.png', $context['options']['site_logo'] );
		$this->assertSame( '555-1234', $context['options']['phone'] );
	}

	/** @test */
	public function sets_empty_options_when_no_field_objects(): void {
		Functions\expect( 'wp_cache_get' )->with( 'theme_options' )->andReturn( false );
		Functions\expect( 'get_field_objects' )->with( 'option' )->andReturn( false );

		$manager = $this->makeActiveManager();
		$context = $manager->add_to_context( [] );

		$this->assertSame( [], $context['options'] );
	}

	/** @test */
	public function preserves_existing_context_keys(): void {
		Functions\expect( 'wp_cache_get' )->with( 'theme_options' )->andReturn( [ 'a' => 1 ] );

		$manager = $this->makeActiveManager();
		$context = $manager->add_to_context( [ 'site' => 'preserved' ] );

		$this->assertSame( 'preserved', $context['site'] );
		$this->assertArrayHasKey( 'options', $context );
	}
}
