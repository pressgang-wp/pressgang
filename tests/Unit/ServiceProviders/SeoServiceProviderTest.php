<?php

namespace PressGang\Tests\Unit\ServiceProviders;

use Brain\Monkey\Functions;
use PressGang\ServiceProviders\SeoServiceProvider;
use PressGang\Tests\Unit\TestCase;

/**
 * @covers \PressGang\ServiceProviders\SeoServiceProvider
 */
class SeoServiceProviderTest extends TestCase {

	/**
	 * Builds a provider with the SEO-plugin detection seam stubbed.
	 */
	private function makeProvider( bool $hasSeoPlugin ): SeoServiceProvider {
		return new class( $hasSeoPlugin ) extends SeoServiceProvider {
			public function __construct( private readonly bool $hasSeoPlugin ) {}

			protected function has_seo_plugin(): bool {
				return $this->hasSeoPlugin;
			}
		};
	}

	public function test_boot_registers_meta_description_on_wp_head_when_enabled(): void {
		Functions\expect( 'apply_filters' )
			->with( 'pressgang_should_render_meta_description', true )
			->once()
			->andReturnUsing( fn() => \func_get_args()[1] );

		Functions\expect( 'apply_filters' )
			->with( 'pressgang_meta_description_priority', 5 )
			->once()
			->andReturn( 5 );

		Functions\expect( 'add_action' )
			->once()
			->with( 'wp_head', \Mockery::type( 'array' ), 5 );

		$this->makeProvider( false )->boot();
	}

	public function test_boot_uses_filterable_priority(): void {
		Functions\expect( 'apply_filters' )
			->with( 'pressgang_should_render_meta_description', true )
			->once()
			->andReturnUsing( fn() => \func_get_args()[1] );

		Functions\expect( 'apply_filters' )
			->with( 'pressgang_meta_description_priority', 5 )
			->once()
			->andReturn( 20 );

		Functions\expect( 'add_action' )
			->once()
			->with( 'wp_head', \Mockery::type( 'array' ), 20 );

		$this->makeProvider( false )->boot();
	}

	public function test_boot_does_not_register_when_seo_plugin_is_detected(): void {
		Functions\expect( 'apply_filters' )
			->with( 'pressgang_should_render_meta_description', false )
			->once()
			->andReturnUsing( fn() => \func_get_args()[1] );

		Functions\expect( 'add_action' )->never();

		$this->makeProvider( true )->boot();
	}

	public function test_render_meta_description_outputs_escaped_tag(): void {
		// Reset MetaDescriptionService's static cache so this test is order-independent.
		$cache = new \ReflectionProperty( \PressGang\SEO\MetaDescriptionService::class, 'meta_description' );
		$cache->setAccessible( true );
		$cache->setValue( null, '' );

		Functions\expect( 'esc_attr' )
			->once()
			->with( 'A description.' )
			->andReturnFirstArg();

		// MetaDescriptionService::get_meta_description() resolves to the site
		// tagline for an unqueried object; stub the WP calls it makes.
		Functions\when( 'get_queried_object' )->justReturn( null );
		Functions\when( 'get_bloginfo' )->justReturn( 'A description.' );

		$this->expectOutputString( '<meta name="description" content="A description.">' . "\n" );

		$this->makeProvider( false )->render_meta_description();
	}
}
