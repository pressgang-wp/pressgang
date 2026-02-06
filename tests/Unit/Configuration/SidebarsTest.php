<?php

namespace PressGang\Tests\Unit\Configuration;

use Brain\Monkey\Functions;
use PressGang\Configuration\Sidebars;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests the Sidebars configuration class: initialization hooks, sidebar
 * registration with defaults, and the pressgang_widget_{key} filter.
 */
class SidebarsTest extends TestCase {

	protected function set_up(): void {
		parent::set_up();
		$this->resetSingletonInstances();
	}

	protected function tear_down(): void {
		$this->resetSingletonInstances();
		parent::tear_down();
	}

	/** @test */
	public function initialize_adds_widget_support_and_hook(): void {
		Functions\expect( 'add_theme_support' )->once()->with( 'widgets' );
		Functions\expect( 'add_action' )
			->once()
			->with( 'widgets_init', \Mockery::type( 'array' ) );

		$sidebars = Sidebars::get_instance();
		$sidebars->initialize( [ 'main' => [ 'name' => 'Main Sidebar' ] ] );
	}

	/** @test */
	public function register_sidebars_calls_register_sidebar_for_each_entry(): void {
		Functions\expect( 'add_theme_support' )->once();
		Functions\expect( 'add_action' )->once();

		$config = [
			'main'   => [ 'name' => 'Main Sidebar', 'id' => 'sidebar-main' ],
			'footer' => [ 'name' => 'Footer', 'id' => 'sidebar-footer' ],
		];

		$sidebars = Sidebars::get_instance();
		$sidebars->initialize( $config );

		// apply_filters returns the args unchanged
		Functions\expect( 'apply_filters' )->andReturnUsing( function ( $hook, $args ) {
			return $args;
		} );

		// wp_parse_args is pre-stubbed by YoastTestCase — it calls array_merge
		Functions\expect( 'register_sidebar' )->twice();

		$sidebars->register_sidebars();
	}

	/** @test */
	public function parse_args_merges_with_defaults(): void {
		Functions\expect( 'add_theme_support' )->once();
		Functions\expect( 'add_action' )->once();

		$sidebars = Sidebars::get_instance();
		$sidebars->initialize( [] );

		$args   = [ 'name' => 'Test', 'before_widget' => '<div>' ];
		$result = $sidebars->parse_args( $args );

		// wp_parse_args is pre-stubbed to work like array_merge( $defaults, $args )
		$this->assertSame( 'Test', $result['name'] );
		$this->assertSame( '<div>', $result['before_widget'] );
		$this->assertArrayHasKey( 'after_widget', $result );
		$this->assertArrayHasKey( 'before_title', $result );
		$this->assertArrayHasKey( 'after_title', $result );
	}

	/** @test */
	public function register_sidebars_applies_widget_filter(): void {
		Functions\expect( 'add_theme_support' )->once();
		Functions\expect( 'add_action' )->once();

		$config = [
			'hero' => [ 'name' => 'Hero Area', 'id' => 'sidebar-hero' ],
		];

		$sidebars = Sidebars::get_instance();
		$sidebars->initialize( $config );

		// The filter modifies the sidebar args
		Functions\expect( 'apply_filters' )
			->once()
			->with( 'pressgang_widget_hero', $config['hero'] )
			->andReturn( [
				'name'          => 'Modified Hero',
				'id'            => 'sidebar-hero',
				'before_widget' => '<section>',
			] );

		Functions\expect( 'register_sidebar' )
			->once()
			->with( \Mockery::on( function ( $args ) {
				return $args['name'] === 'Modified Hero' && $args['before_widget'] === '<section>';
			} ) );

		$sidebars->register_sidebars();
	}

	/** @test */
	public function register_sidebars_skips_entry_when_filter_returns_non_array(): void {
		Functions\expect( 'add_theme_support' )->once();
		Functions\expect( 'add_action' )->once();

		$config = [
			'disabled' => [ 'name' => 'Will Be Disabled' ],
		];

		$sidebars = Sidebars::get_instance();
		$sidebars->initialize( $config );

		Functions\expect( 'apply_filters' )
			->once()
			->with( 'pressgang_widget_disabled', $config['disabled'] )
			->andReturn( false );

		Functions\expect( 'register_sidebar' )->never();

		$sidebars->register_sidebars();
	}

	/** @test */
	public function get_instance_returns_singleton(): void {
		Functions\expect( 'add_theme_support' )->once();
		Functions\expect( 'add_action' )->once();

		$a = Sidebars::get_instance();
		$a->initialize( [] );
		$b = Sidebars::get_instance();

		$this->assertSame( $a, $b );
	}
}
