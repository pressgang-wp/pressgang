<?php

namespace PressGang\Tests\Unit\ContextManagers;

use Brain\Monkey\Functions;
use PressGang\ContextManagers\MenuContextManager;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests MenuContextManager: populating context with nav menus,
 * skipping unassigned locations, and applying per-location filters.
 *
 * Note: Timber::get_menu() is a static call on an already-loaded class,
 * so we run each test in a separate process to allow Mockery aliasing.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class MenuContextManagerTest extends TestCase {

	/** @test */
	public function adds_assigned_menus_to_context(): void {
		Functions\expect( 'get_registered_nav_menus' )->once()->andReturn( [
			'primary' => 'Primary Menu',
			'footer'  => 'Footer Menu',
		] );

		Functions\expect( 'has_nav_menu' )->andReturn( true );

		$timber = \Mockery::mock( 'alias:Timber\Timber' );
		$timber->shouldReceive( 'get_menu' )->with( 'primary' )->andReturn( (object) [ 'id' => 'primary' ] );
		$timber->shouldReceive( 'get_menu' )->with( 'footer' )->andReturn( (object) [ 'id' => 'footer' ] );

		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$manager = new MenuContextManager();
		$context = $manager->add_to_context( [] );

		$this->assertArrayHasKey( 'menu_primary', $context );
		$this->assertArrayHasKey( 'menu_footer', $context );
		$this->assertSame( 'primary', $context['menu_primary']->id );
		$this->assertSame( 'footer', $context['menu_footer']->id );
	}

	/** @test */
	public function skips_unassigned_menu_locations(): void {
		Functions\expect( 'get_registered_nav_menus' )->once()->andReturn( [
			'primary'   => 'Primary Menu',
			'secondary' => 'Secondary Menu',
		] );

		Functions\expect( 'has_nav_menu' )->andReturnUsing( function ( $location ) {
			return $location === 'primary';
		} );

		$timber = \Mockery::mock( 'alias:Timber\Timber' );
		$timber->shouldReceive( 'get_menu' )->with( 'primary' )->andReturn( (object) [ 'id' => 'primary' ] );

		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$manager = new MenuContextManager();
		$context = $manager->add_to_context( [] );

		$this->assertArrayHasKey( 'menu_primary', $context );
		$this->assertArrayNotHasKey( 'menu_secondary', $context );
	}

	/** @test */
	public function no_registered_menus_returns_context_unchanged(): void {
		Functions\expect( 'get_registered_nav_menus' )->once()->andReturn( [] );

		$manager  = new MenuContextManager();
		$original = [ 'site' => 'test' ];
		$context  = $manager->add_to_context( $original );

		$this->assertSame( $original, $context );
	}

	/** @test */
	public function filter_can_replace_menu_object(): void {
		Functions\expect( 'get_registered_nav_menus' )->once()->andReturn( [
			'primary' => 'Primary',
		] );

		Functions\expect( 'has_nav_menu' )->andReturn( true );

		$timber = \Mockery::mock( 'alias:Timber\Timber' );
		$timber->shouldReceive( 'get_menu' )->with( 'primary' )->andReturn( (object) [ 'name' => 'Original' ] );

		Functions\expect( 'apply_filters' )
			->with( 'pressgang_context_menu_primary', \Mockery::any() )
			->andReturn( (object) [ 'name' => 'Filtered' ] );

		$manager = new MenuContextManager();
		$context = $manager->add_to_context( [] );

		$this->assertSame( 'Filtered', $context['menu_primary']->name );
	}

	/** @test */
	public function preserves_existing_context_keys(): void {
		Functions\expect( 'get_registered_nav_menus' )->once()->andReturn( [
			'main' => 'Main Menu',
		] );

		Functions\expect( 'has_nav_menu' )->andReturn( true );

		$timber = \Mockery::mock( 'alias:Timber\Timber' );
		$timber->shouldReceive( 'get_menu' )->with( 'main' )->andReturn( (object) [ 'id' => 'main' ] );

		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$manager = new MenuContextManager();
		$context = $manager->add_to_context( [ 'site' => 'existing' ] );

		$this->assertSame( 'existing', $context['site'] );
		$this->assertSame( 'main', $context['menu_main']->id );
	}
}
