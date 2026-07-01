<?php

namespace PressGang\Tests\Unit\ContextManagers;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\ContextManagers\AcfOptionsContextManager;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests AcfOptionsContextManager: ACF availability, raw field-object caching,
 * field mapping, and cache invalidation.
 */
class AcfOptionsContextManagerTest extends TestCase {

	private function makeInactiveManager(): AcfOptionsContextManager {
		return new TestableAcfOptionsContextManager( false );
	}

	private function makeActiveManager(): AcfOptionsContextManager {
		return new TestableAcfOptionsContextManager( true );
	}

	/** @test */
	public function registers_acf_save_invalidation_hook(): void {
		Actions\expectAdded( 'acf/save_post' )->once();

		$this->makeActiveManager();
	}

	/** @test */
	public function returns_context_unchanged_when_acf_not_active(): void {
		$original = [ 'site' => 'test' ];

		Actions\expectAdded( 'acf/save_post' )->once();
		Functions\expect( 'wp_cache_get' )->never();

		$manager = $this->makeInactiveManager();
		$context = $manager->add_to_context( $original );

		$this->assertSame( $original, $context );
		$this->assertArrayNotHasKey( 'options', $context );
	}

	/** @test */
	public function returns_mapped_options_from_cached_raw_field_objects(): void {
		$field_objects = [
			'site_logo' => [ 'type' => 'image', 'value' => 'logo.png' ],
			'phone'     => [ 'type' => 'text', 'value' => '555-1234' ],
		];

		Actions\expectAdded( 'acf/save_post' )->once();
		Functions\expect( 'wp_cache_get' )->with( 'theme_option_field_objects', 'pressgang' )->andReturn( $field_objects );
		Functions\expect( 'get_field_objects' )->never();
		Functions\expect( 'wp_cache_set' )->never();

		$manager = $this->makeActiveManager();
		$context = $manager->add_to_context( [] );

		$this->assertSame( 'logo.png', $context['options']['site_logo'] );
		$this->assertSame( '555-1234', $context['options']['phone'] );
	}

	/** @test */
	public function loads_and_caches_raw_field_objects_on_cache_miss(): void {
		$field_objects = [
			'site_logo' => [ 'type' => 'image', 'value' => 'logo.png' ],
			'phone'     => [ 'type' => 'text', 'value' => '555-1234' ],
		];

		Actions\expectAdded( 'acf/save_post' )->once();
		Functions\expect( 'wp_cache_get' )->with( 'theme_option_field_objects', 'pressgang' )->andReturn( false );
		Functions\expect( 'get_field_objects' )->with( 'option' )->andReturn( $field_objects );
		Functions\expect( 'wp_cache_set' )->once()->with( 'theme_option_field_objects', $field_objects, 'pressgang' );

		$manager = $this->makeActiveManager();
		$context = $manager->add_to_context( [] );

		$this->assertSame( 'logo.png', $context['options']['site_logo'] );
		$this->assertSame( '555-1234', $context['options']['phone'] );
	}

	/** @test */
	public function sets_empty_options_when_no_field_objects(): void {
		Actions\expectAdded( 'acf/save_post' )->once();
		Functions\expect( 'wp_cache_get' )->with( 'theme_option_field_objects', 'pressgang' )->andReturn( false );
		Functions\expect( 'get_field_objects' )->with( 'option' )->andReturn( false );
		Functions\expect( 'wp_cache_set' )->once()->with( 'theme_option_field_objects', [], 'pressgang' );

		$manager = $this->makeActiveManager();
		$context = $manager->add_to_context( [] );

		$this->assertSame( [], $context['options'] );
	}

	/** @test */
	public function preserves_existing_context_keys(): void {
		Actions\expectAdded( 'acf/save_post' )->once();
		Functions\expect( 'wp_cache_get' )->with( 'theme_option_field_objects', 'pressgang' )->andReturn(
			[ 'a' => [ 'type' => 'text', 'value' => 1 ] ]
		);

		$manager = $this->makeActiveManager();
		$context = $manager->add_to_context( [ 'site' => 'preserved' ] );

		$this->assertSame( 'preserved', $context['site'] );
		$this->assertArrayHasKey( 'options', $context );
	}

	/** @test */
	public function invalidate_cache_deletes_the_cache_key(): void {
		Actions\expectAdded( 'acf/save_post' )->once();
		Functions\expect( 'wp_cache_delete' )->once()->with( 'theme_option_field_objects', 'pressgang' );

		$manager = $this->makeActiveManager();
		$manager->invalidate_cache();
	}
}

class TestableAcfOptionsContextManager extends AcfOptionsContextManager {

	public function __construct( private readonly bool $active ) {
		parent::__construct();
	}

	protected function is_acf_active(): bool {
		return $this->active;
	}

	protected function map_field( array $field ): mixed {
		return $field['value'] ?? null;
	}
}
