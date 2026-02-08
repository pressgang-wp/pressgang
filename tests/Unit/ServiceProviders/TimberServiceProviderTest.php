<?php

namespace PressGang\Tests\Unit\ServiceProviders;

use Brain\Monkey\Functions;
use PressGang\Bootstrap\Config;
use PressGang\Bootstrap\ConfigLoaderInterface;
use PressGang\ContextManagers\ContextManagerInterface;
use PressGang\ServiceProviders\TimberServiceProvider;
use PressGang\TwigExtensions\TwigExtensionManagerInterface;
use PressGang\Tests\Unit\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * Tests TimberServiceProvider: boot-time hook registration, context manager
 * pipeline, Twig extension pipeline, and snippet path registration.
 */
class TimberServiceProviderTest extends TestCase {

	protected function tear_down(): void {
		Config::clear_cache();
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
	public function boot_registers_timber_context_and_twig_filters(): void {
		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( [
			'context-managers' => [],
			'twig-extensions'  => [],
		] );

		Config::set_loader( $configLoader );
		$this->stubApplyFilters();

		Functions\expect( 'add_filter' )
			->with( 'timber/context', \Mockery::type( 'array' ) )
			->once();

		Functions\expect( 'add_filter' )
			->with( 'timber/twig', \Mockery::type( 'array' ) )
			->once();

		Functions\expect( 'add_filter' )
			->with( 'timber/locations', \Mockery::type( 'Closure' ) )
			->once();

		$provider = new TimberServiceProvider();
		$provider->boot();
	}

	/** @test */
	public function boot_instantiates_context_managers_from_config(): void {
		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( [
			'context-managers' => [ StubContextManager::class ],
			'twig-extensions'  => [],
		] );

		Config::set_loader( $configLoader );
		$this->stubApplyFilters();
		Functions\expect( 'add_filter' )->zeroOrMoreTimes();

		$provider = new TimberServiceProvider();
		$provider->boot();

		// Invoke the context pipeline — our stub should add 'stub_key'
		$context = $provider->add_to_context( [] );
		$this->assertArrayHasKey( 'stub_key', $context );
		$this->assertSame( 'stub_value', $context['stub_key'] );
	}

	/** @test */
	public function boot_skips_nonexistent_context_manager_class(): void {
		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( [
			'context-managers' => [ 'NonExistent\\ContextManager' ],
			'twig-extensions'  => [],
		] );

		Config::set_loader( $configLoader );
		$this->stubApplyFilters();
		Functions\expect( 'add_filter' )->zeroOrMoreTimes();

		$provider = new TimberServiceProvider();
		$provider->boot();

		// Pipeline should be empty — context passes through unchanged
		$context = $provider->add_to_context( [ 'existing' => true ] );
		$this->assertSame( [ 'existing' => true ], $context );
	}

	/** @test */
	public function add_to_context_chains_multiple_managers(): void {
		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( [
			'context-managers' => [
				StubContextManager::class,
				SecondStubContextManager::class,
			],
			'twig-extensions' => [],
		] );

		Config::set_loader( $configLoader );
		$this->stubApplyFilters();
		Functions\expect( 'add_filter' )->zeroOrMoreTimes();

		$provider = new TimberServiceProvider();
		$provider->boot();

		$context = $provider->add_to_context( [ 'original' => true ] );

		$this->assertTrue( $context['original'] );
		$this->assertSame( 'stub_value', $context['stub_key'] );
		$this->assertSame( 'second_value', $context['second_key'] );
	}

	/** @test */
	public function boot_instantiates_twig_extension_managers_from_config(): void {
		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( [
			'context-managers' => [],
			'twig-extensions'  => [ StubTwigExtensionManager::class ],
		] );

		Config::set_loader( $configLoader );
		$this->stubApplyFilters();
		Functions\expect( 'add_filter' )->zeroOrMoreTimes();

		$provider = new TimberServiceProvider();
		$provider->boot();

		$twig = new Environment( new ArrayLoader() );
		$twig = $provider->add_to_twig( $twig );

		$this->assertTrue( $twig->getFunction( 'stub_function' ) !== false );
	}

	/** @test */
	public function add_to_twig_calls_all_three_methods_on_each_manager(): void {
		$manager = $this->createMock( TwigExtensionManagerInterface::class );
		$manager->expects( $this->once() )->method( 'add_twig_functions' );
		$manager->expects( $this->once() )->method( 'add_twig_filters' );
		$manager->expects( $this->once() )->method( 'add_twig_globals' );

		// Use reflection to inject the mock directly
		$provider   = new TimberServiceProvider();
		$reflection = new \ReflectionClass( $provider );
		$property   = $reflection->getProperty( 'twig_extensions' );
		$property->setValue( $provider, [ $manager ] );

		$twig = new Environment( new ArrayLoader() );
		$provider->add_to_twig( $twig );
	}

	/** @test */
	public function register_snippets_adds_timber_locations_filter(): void {
		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( [
			'context-managers' => [],
			'twig-extensions'  => [],
		] );

		Config::set_loader( $configLoader );
		$this->stubApplyFilters();

		Functions\expect( 'add_filter' )
			->with( 'timber/context', \Mockery::any() )
			->once();

		Functions\expect( 'add_filter' )
			->with( 'timber/twig', \Mockery::any() )
			->once();

		Functions\expect( 'add_filter' )
			->with( 'timber/locations', \Mockery::type( 'Closure' ) )
			->once();

		$provider = new TimberServiceProvider();
		$provider->boot();
	}
}

/**
 * Minimal stub for testing the context manager pipeline.
 */
class StubContextManager implements ContextManagerInterface {
	public function add_to_context( array $context ): array {
		$context['stub_key'] = 'stub_value';
		return $context;
	}
}

/**
 * Second stub to verify chaining.
 */
class SecondStubContextManager implements ContextManagerInterface {
	public function add_to_context( array $context ): array {
		$context['second_key'] = 'second_value';
		return $context;
	}
}

/**
 * Minimal stub for testing the Twig extension pipeline.
 */
class StubTwigExtensionManager implements TwigExtensionManagerInterface {
	public function add_twig_functions( Environment $twig ): void {
		$twig->addFunction( new \Twig\TwigFunction( 'stub_function', function () {
			return 'stub';
		} ) );
	}

	public function add_twig_filters( Environment $twig ): void {
	}

	public function add_twig_globals( Environment $twig ): void {
	}
}
