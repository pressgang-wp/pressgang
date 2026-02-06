<?php

namespace PressGang\Tests\Unit\ContextManagers;

use Brain\Monkey\Functions;
use PressGang\ContextManagers\ThemeModsContextManager;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests ThemeModsContextManager: populating context['theme'] with theme_mods,
 * applying per-key filters, and handling empty/missing mods.
 */
class ThemeModsContextManagerTest extends TestCase {

	/** @test */
	public function adds_theme_mods_to_context_theme_object(): void {
		Functions\expect( 'get_theme_mods' )->once()->andReturn( [
			'header_image' => 'http://example.com/header.jpg',
			'accent_color' => '#ff0000',
		] );

		// Use andReturnUsing to pass through the value for each filter call
		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$theme   = new \stdClass();
		$manager = new ThemeModsContextManager();
		$context = $manager->add_to_context( [ 'theme' => $theme ] );

		$this->assertSame( 'http://example.com/header.jpg', $context['theme']->header_image );
		$this->assertSame( '#ff0000', $context['theme']->accent_color );
	}

	/** @test */
	public function filter_can_modify_individual_theme_mod(): void {
		Functions\expect( 'get_theme_mods' )->once()->andReturn( [
			'logo' => 'original.png',
		] );

		Functions\expect( 'apply_filters' )
			->with( 'pressgang_theme_mod_logo', 'original.png' )
			->andReturn( 'filtered.svg' );

		$theme   = new \stdClass();
		$manager = new ThemeModsContextManager();
		$context = $manager->add_to_context( [ 'theme' => $theme ] );

		$this->assertSame( 'filtered.svg', $context['theme']->logo );
	}

	/** @test */
	public function returns_context_unchanged_when_no_theme_mods(): void {
		Functions\expect( 'get_theme_mods' )->once()->andReturn( false );

		$theme    = new \stdClass();
		$original = [ 'theme' => $theme, 'site' => 'test' ];

		$manager = new ThemeModsContextManager();
		$context = $manager->add_to_context( $original );

		$this->assertSame( $original, $context );
	}

	/** @test */
	public function returns_context_unchanged_when_theme_mods_empty_array(): void {
		Functions\expect( 'get_theme_mods' )->once()->andReturn( [] );

		$theme    = new \stdClass();
		$original = [ 'theme' => $theme ];

		$manager = new ThemeModsContextManager();
		$context = $manager->add_to_context( $original );

		$this->assertSame( $original, $context );
	}

	/** @test */
	public function preserves_existing_context_keys(): void {
		Functions\expect( 'get_theme_mods' )->once()->andReturn( [
			'color' => 'blue',
		] );

		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$theme   = new \stdClass();
		$manager = new ThemeModsContextManager();
		$context = $manager->add_to_context( [ 'theme' => $theme, 'site' => 'preserved' ] );

		$this->assertSame( 'preserved', $context['site'] );
		$this->assertSame( 'blue', $context['theme']->color );
	}
}
