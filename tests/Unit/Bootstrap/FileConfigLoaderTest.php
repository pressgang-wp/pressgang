<?php

namespace PressGang\Tests\Unit\Bootstrap;

use Brain\Monkey\Functions;
use PressGang\Bootstrap\FileConfigLoader;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests FileConfigLoader: caching behaviour (wp_cache / transient) and
 * directory-based config loading with parent/child merge semantics.
 *
 * Note: the transient-based test defines PRESSGANG_CONFIG_CACHE_SECONDS which
 * persists for the rest of the process. Tests that run after it must stub
 * get_transient instead of wp_cache_get.
 */
class FileConfigLoaderTest extends TestCase {

	/**
	 * Stubs the appropriate cache-get function depending on whether
	 * PRESSGANG_CONFIG_CACHE_SECONDS has been defined.
	 *
	 * @param mixed $return
	 */
	private function stubCacheGet( mixed $return ): void {
		if ( defined( 'PRESSGANG_CONFIG_CACHE_SECONDS' ) && PRESSGANG_CONFIG_CACHE_SECONDS ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( FileConfigLoader::CACHE_KEY )
				->andReturn( $return );
		} else {
			Functions\expect( 'wp_cache_get' )
				->once()
				->with( FileConfigLoader::CACHE_KEY, FileConfigLoader::CACHE_GROUP )
				->andReturn( $return );
		}
	}

	/**
	 * Stubs the appropriate cache-set function.
	 */
	private function stubCacheSet(): void {
		if ( defined( 'PRESSGANG_CONFIG_CACHE_SECONDS' ) && PRESSGANG_CONFIG_CACHE_SECONDS ) {
			Functions\expect( 'set_transient' )->once();
		} else {
			Functions\expect( 'wp_cache_set' )->once();
		}
	}

	/** @test */
	public function load_returns_cached_settings(): void {
		$cached = [ 'sidebars' => [ 'main' => [] ] ];

		$this->stubCacheGet( $cached );

		$loader = new FileConfigLoader();
		$this->assertSame( $cached, $loader->load() );
	}

	/** @test */
	public function load_falls_through_when_cache_returns_false(): void {
		$this->stubCacheGet( false );

		Functions\expect( 'get_template_directory' )->andReturn( '/parent' );
		Functions\expect( 'get_stylesheet_directory' )->andReturn( '/child' );
		Functions\expect( 'apply_filters' )
			->with( 'pressgang_config_directories', \Mockery::type( 'array' ) )
			->andReturnUsing( function () {
				$args = func_get_args();
				return $args[1];
			} );

		$this->stubCacheSet();

		$loader = new FileConfigLoader();
		$this->assertSame( [], $loader->load() );
	}

	/** @test */
	public function config_directories_filter_is_applied(): void {
		$this->stubCacheGet( false );

		Functions\expect( 'get_template_directory' )->andReturn( '/parent' );
		Functions\expect( 'get_stylesheet_directory' )->andReturn( '/child' );

		Functions\expect( 'apply_filters' )
			->with( 'pressgang_config_directories', \Mockery::type( 'array' ) )
			->once()
			->andReturn( [] ); // Filter removes all directories

		$this->stubCacheSet();

		$loader = new FileConfigLoader();
		$this->assertSame( [], $loader->load() );
	}

	/** @test */
	public function load_uses_transients_when_cache_seconds_defined(): void {
		$cached = [ 'menus' => [ 'primary' => 'Primary' ] ];

		if ( ! defined( 'PRESSGANG_CONFIG_CACHE_SECONDS' ) ) {
			define( 'PRESSGANG_CONFIG_CACHE_SECONDS', 3600 );
		}

		Functions\expect( 'get_transient' )
			->once()
			->with( FileConfigLoader::CACHE_KEY )
			->andReturn( $cached );

		$loader = new FileConfigLoader();
		$this->assertSame( $cached, $loader->load() );
	}
}
