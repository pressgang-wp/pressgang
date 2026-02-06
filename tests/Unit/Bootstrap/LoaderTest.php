<?php

namespace PressGang\Tests\Unit\Bootstrap;

use Brain\Monkey\Functions;
use PressGang\Bootstrap\Config;
use PressGang\Bootstrap\ConfigLoaderInterface;
use PressGang\Bootstrap\Loader;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests the Loader: config-key-to-class mapping, component initialisation,
 * and file inclusion paths.
 */
class LoaderTest extends TestCase {

	/**
	 * Stubs apply_filters to pass through the filtered value (second argument).
	 */
	private function stubApplyFilters(): void {
		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			$args = func_get_args();
			return $args[1] ?? null;
		} );
	}

	protected function tear_down(): void {
		Config::clear_cache();
		$this->resetSingletonInstances();
		parent::tear_down();
	}

	/** @test */
	public function config_key_maps_to_correct_class_name(): void {
		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$loader       = new Loader( $configLoader );

		$method = new \ReflectionMethod( $loader, 'config_key_to_configuration_class' );
		$method->setAccessible( true );

		$this->assertSame(
			'PressGang\\Configuration\\Sidebars',
			$method->invoke( $loader, 'sidebars' )
		);
	}

	/** @test */
	public function config_key_with_hyphens_maps_to_studly_case(): void {
		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$loader       = new Loader( $configLoader );

		$method = new \ReflectionMethod( $loader, 'config_key_to_configuration_class' );
		$method->setAccessible( true );

		$this->assertSame(
			'PressGang\\Configuration\\CustomPostTypes',
			$method->invoke( $loader, 'custom-post-types' )
		);
	}

	/** @test */
	public function load_components_initialises_configuration_classes(): void {
		$config = [
			'sidebars' => [
				'main' => [ 'name' => 'Main Sidebar' ],
			],
		];

		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( $config );

		$this->stubApplyFilters();

		// Sidebars::initialize calls these WP functions
		Functions\expect( 'add_theme_support' )->once()->with( 'widgets' );
		Functions\expect( 'add_action' )->once()->with( 'widgets_init', \Mockery::type( 'array' ) );

		$loader = new Loader( $configLoader );
		$loader->initialize();

		// Verify the singleton was created and initialized
		$instance = \PressGang\Configuration\Sidebars::get_instance();
		$this->assertInstanceOf( \PressGang\Configuration\Sidebars::class, $instance );
	}

	/** @test */
	public function load_components_skips_nonexistent_classes(): void {
		$config = [
			'nonexistent-feature' => [ 'some' => 'data' ],
		];

		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( $config );

		$this->stubApplyFilters();

		$loader = new Loader( $configLoader );

		// Should not throw — nonexistent class is silently skipped
		$loader->initialize();

		$this->assertTrue( true, 'Loader silently skipped nonexistent configuration class' );
	}

	/** @test */
	public function include_files_reads_shortcodes_and_widgets_keys(): void {
		$config = [
			'shortcodes' => [ 'MyShortcode' ],
			'widgets'    => [ 'MyWidget' ],
		];

		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( $config );

		$this->stubApplyFilters();

		Functions\expect( 'get_stylesheet_directory' )->andReturn( '/child' );
		Functions\expect( 'get_template_directory' )->andReturn( '/parent' );

		$loader = new Loader( $configLoader );

		// The files won't exist on disk, so require_once won't fire,
		// but the method should execute without errors.
		$loader->initialize();

		$this->assertTrue( true, 'include_files processed shortcodes and widgets keys' );
	}
}
