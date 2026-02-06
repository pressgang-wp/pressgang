<?php

namespace PressGang\Tests\Unit\Configuration;

use Brain\Monkey\Functions;
use PressGang\Configuration\Menus;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests the Menus configuration class: initialization hook registration,
 * nav menu registration with per-location filters, and filter-based removal.
 */
class MenusTest extends TestCase {

	protected function set_up(): void {
		parent::set_up();
		$this->resetSingletonInstances();
	}

	protected function tear_down(): void {
		$this->resetSingletonInstances();
		parent::tear_down();
	}

	/** @test */
	public function initialize_registers_init_action(): void {
		Functions\expect( 'add_action' )
			->once()
			->with( 'init', \Mockery::type( 'array' ) );

		$menus = Menus::get_instance();
		$menus->initialize( [ 'primary' => 'Primary Menu' ] );
	}

	/** @test */
	public function register_nav_menus_passes_config_to_wordpress(): void {
		Functions\expect( 'add_action' )->once();

		$config = [
			'primary' => 'Primary Menu',
			'footer'  => 'Footer Menu',
		];

		$menus = Menus::get_instance();
		$menus->initialize( $config );

		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		Functions\expect( 'register_nav_menus' )
			->once()
			->with( [
				'primary' => 'Primary Menu',
				'footer'  => 'Footer Menu',
			] );

		$menus->register_nav_menus();
	}

	/** @test */
	public function per_location_filter_can_modify_menu(): void {
		Functions\expect( 'add_action' )->once();

		$config = [ 'primary' => 'Primary' ];

		$menus = Menus::get_instance();
		$menus->initialize( $config );

		Functions\expect( 'apply_filters' )
			->with( 'pressgang_register_menu_primary', [ 'primary' => 'Primary' ] )
			->once()
			->andReturn( [ 'primary' => 'Renamed Primary' ] );

		Functions\expect( 'register_nav_menus' )
			->once()
			->with( [ 'primary' => 'Renamed Primary' ] );

		$menus->register_nav_menus();
	}

	/** @test */
	public function filter_returning_empty_array_skips_menu(): void {
		Functions\expect( 'add_action' )->once();

		$config = [
			'primary' => 'Primary',
			'footer'  => 'Footer',
		];

		$menus = Menus::get_instance();
		$menus->initialize( $config );

		// Use andReturnUsing to handle both filter calls dynamically
		Functions\expect( 'apply_filters' )->andReturnUsing( function ( $hook, $value ) {
			if ( $hook === 'pressgang_register_menu_primary' ) {
				return []; // skip primary
			}
			return $value; // pass through footer
		} );

		Functions\expect( 'register_nav_menus' )
			->once()
			->with( [ 'footer' => 'Footer' ] );

		$menus->register_nav_menus();
	}

	/** @test */
	public function empty_config_registers_no_menus(): void {
		Functions\expect( 'add_action' )->once();

		$menus = Menus::get_instance();
		$menus->initialize( [] );

		Functions\expect( 'register_nav_menus' )
			->once()
			->with( [] );

		$menus->register_nav_menus();
	}
}
