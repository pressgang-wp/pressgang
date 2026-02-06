<?php

namespace PressGang\Tests\Unit\Bootstrap;

use Brain\Monkey\Functions;
use PressGang\Bootstrap\Config;
use PressGang\Bootstrap\ConfigLoaderInterface;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests the Config static facade: lazy loading, key retrieval, defaults, and cache clearing.
 */
class ConfigTest extends TestCase {

	protected function tear_down(): void {
		Config::clear_cache();
		Config::set_loader( $this->createStub( ConfigLoaderInterface::class ) );
		parent::tear_down();
	}

	/**
	 * Stubs apply_filters to pass through the filtered value (second argument).
	 */
	private function stubApplyFilters(): void {
		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			$args = func_get_args();
			return $args[1] ?? null;
		} );
	}

	/** @test */
	public function get_returns_all_settings_when_no_key_given(): void {
		$settings = [ 'sidebars' => [ 'main' => [] ], 'menus' => [ 'primary' => 'Primary' ] ];

		$loader = $this->createMock( ConfigLoaderInterface::class );
		$loader->expects( $this->once() )->method( 'load' )->willReturn( $settings );

		Config::set_loader( $loader );
		$this->stubApplyFilters();

		$this->assertSame( $settings, Config::get() );
	}

	/** @test */
	public function get_returns_single_key(): void {
		$settings = [ 'sidebars' => [ 'main' => [] ] ];

		$loader = $this->createStub( ConfigLoaderInterface::class );
		$loader->method( 'load' )->willReturn( $settings );

		Config::set_loader( $loader );
		$this->stubApplyFilters();

		$this->assertSame( [ 'main' => [] ], Config::get( 'sidebars' ) );
	}

	/** @test */
	public function get_returns_default_for_missing_key(): void {
		$loader = $this->createStub( ConfigLoaderInterface::class );
		$loader->method( 'load' )->willReturn( [] );

		Config::set_loader( $loader );
		$this->stubApplyFilters();

		$this->assertSame( 'fallback', Config::get( 'nonexistent', 'fallback' ) );
	}

	/** @test */
	public function get_default_is_empty_array_when_not_specified(): void {
		$loader = $this->createStub( ConfigLoaderInterface::class );
		$loader->method( 'load' )->willReturn( [] );

		Config::set_loader( $loader );
		$this->stubApplyFilters();

		$this->assertSame( [], Config::get( 'nonexistent' ) );
	}

	/** @test */
	public function settings_are_lazy_loaded_only_once(): void {
		$loader = $this->createMock( ConfigLoaderInterface::class );
		$loader->expects( $this->once() )->method( 'load' )->willReturn( [ 'a' => 1 ] );

		Config::set_loader( $loader );
		$this->stubApplyFilters();

		Config::get();
		Config::get();
		Config::get( 'a' );
	}

	/** @test */
	public function clear_cache_forces_reload(): void {
		$loader = $this->createMock( ConfigLoaderInterface::class );
		$loader->expects( $this->exactly( 2 ) )->method( 'load' )->willReturn( [ 'a' => 1 ] );

		Config::set_loader( $loader );
		$this->stubApplyFilters();

		Config::get();
		Config::clear_cache();
		Config::get();
	}

	/** @test */
	public function pressgang_get_config_filter_can_modify_settings(): void {
		$original = [ 'menus' => [ 'primary' => 'Primary' ] ];
		$filtered = [ 'menus' => [ 'primary' => 'Primary' ], 'injected' => true ];

		$loader = $this->createStub( ConfigLoaderInterface::class );
		$loader->method( 'load' )->willReturn( $original );

		Config::set_loader( $loader );

		Functions\expect( 'apply_filters' )
			->once()
			->with( 'pressgang_get_config', $original )
			->andReturn( $filtered );

		$this->assertTrue( Config::get( 'injected' ) );
	}
}
