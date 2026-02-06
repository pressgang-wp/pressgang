<?php

namespace PressGang\Tests\Unit\ContextManagers;

use Brain\Monkey\Functions;
use PressGang\ContextManagers\MenuContextManager;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests MenuContextManager: populating context with nav menus,
 * skipping unassigned locations, and applying per-location filters.
 *
 * Uses an anonymous subclass to override get_menu(), avoiding the need
 * for separate-process execution to mock Timber::get_menu().
 */
class MenuContextManagerTest extends TestCase {

	/**
	 * Creates a testable MenuContextManager whose get_menu() returns from a lookup map.
	 *
	 * @param array<string, mixed> $menuMap location => menu object
	 *
	 * @return MenuContextManager
	 */
	private function makeManager( array $menuMap ): MenuContextManager {
		return new class( $menuMap ) extends MenuContextManager {
			public function __construct( private readonly array $menuMap ) {
			}

			protected function get_menu( string $location ): ?object {
				return $this->menuMap[ $location ] ?? null;
			}
		};
	}

	/** @test */
	public function adds_assigned_menus_to_context(): void {
		Functions\expect( 'get_registered_nav_menus' )->once()->andReturn( [
			'primary' => 'Primary Menu',
			'footer'  => 'Footer Menu',
		] );

		Functions\expect( 'has_nav_menu' )->andReturn( true );

		$primaryMenu = (object) [ 'id' => 'primary' ];
		$footerMenu  = (object) [ 'id' => 'footer' ];

		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$manager = $this->makeManager( [
			'primary' => $primaryMenu,
			'footer'  => $footerMenu,
		] );

		$context = $manager->add_to_context( [] );

		$this->assertSame( $primaryMenu, $context['menu_primary'] );
		$this->assertSame( $footerMenu, $context['menu_footer'] );
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

		$primaryMenu = (object) [ 'id' => 'primary' ];

		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$manager = $this->makeManager( [ 'primary' => $primaryMenu ] );
		$context = $manager->add_to_context( [] );

		$this->assertArrayHasKey( 'menu_primary', $context );
		$this->assertArrayNotHasKey( 'menu_secondary', $context );
	}

	/** @test */
	public function no_registered_menus_returns_context_unchanged(): void {
		Functions\expect( 'get_registered_nav_menus' )->once()->andReturn( [] );

		$manager  = $this->makeManager( [] );
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

		$originalMenu = (object) [ 'name' => 'Original' ];
		$filteredMenu = (object) [ 'name' => 'Filtered' ];

		Functions\expect( 'apply_filters' )
			->with( 'pressgang_context_menu_primary', $originalMenu )
			->andReturn( $filteredMenu );

		$manager = $this->makeManager( [ 'primary' => $originalMenu ] );
		$context = $manager->add_to_context( [] );

		$this->assertSame( $filteredMenu, $context['menu_primary'] );
	}

	/** @test */
	public function preserves_existing_context_keys(): void {
		Functions\expect( 'get_registered_nav_menus' )->once()->andReturn( [
			'main' => 'Main Menu',
		] );

		Functions\expect( 'has_nav_menu' )->andReturn( true );

		$menu = (object) [ 'id' => 'main' ];

		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$manager = $this->makeManager( [ 'main' => $menu ] );
		$context = $manager->add_to_context( [ 'site' => 'existing' ] );

		$this->assertSame( 'existing', $context['site'] );
		$this->assertSame( $menu, $context['menu_main'] );
	}
}
