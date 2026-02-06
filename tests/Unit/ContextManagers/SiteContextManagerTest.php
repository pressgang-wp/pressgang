<?php

namespace PressGang\Tests\Unit\ContextManagers;

use Brain\Monkey\Functions;
use PressGang\ContextManagers\SiteContextManager;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests SiteContextManager: adds a site object with a cache-busted stylesheet
 * to the context. Uses an anonymous subclass to stub make_site().
 */
class SiteContextManagerTest extends TestCase {

	/**
	 * Creates a testable SiteContextManager whose make_site() returns a plain stdClass.
	 *
	 * @return SiteContextManager
	 */
	private function makeManager(): SiteContextManager {
		return new class extends SiteContextManager {
			protected function make_site(): object {
				return new \stdClass();
			}
		};
	}

	/** @test */
	public function adds_site_to_context(): void {
		Functions\expect( 'get_stylesheet_directory' )->andReturn( '/theme' );
		Functions\expect( 'get_stylesheet_directory_uri' )->andReturn( 'https://example.com/theme' );
		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$manager = $this->makeManager();
		$context = $manager->add_to_context( [] );

		$this->assertArrayHasKey( 'site', $context );
		$this->assertIsObject( $context['site'] );
	}

	/** @test */
	public function sets_stylesheet_on_site_object(): void {
		Functions\expect( 'get_stylesheet_directory' )->andReturn( '/theme' );
		Functions\expect( 'get_stylesheet_directory_uri' )->andReturn( 'https://example.com/theme' );
		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$manager = $this->makeManager();
		$context = $manager->add_to_context( [] );

		$this->assertObjectHasProperty( 'stylesheet', $context['site'] );
		$this->assertStringContains( '/css/styles.css', $context['site']->stylesheet );
	}

	/** @test */
	public function stylesheet_filter_is_applied(): void {
		Functions\expect( 'get_stylesheet_directory' )->andReturn( '/theme' );
		Functions\expect( 'get_stylesheet_directory_uri' )->andReturn( 'https://example.com/theme' );

		Functions\expect( 'apply_filters' )
			->with( 'pressgang_stylesheet', \Mockery::type( 'string' ) )
			->once()
			->andReturn( 'https://cdn.example.com/custom.css' );

		$manager = $this->makeManager();
		$context = $manager->add_to_context( [] );

		$this->assertSame( 'https://cdn.example.com/custom.css', $context['site']->stylesheet );
	}

	/** @test */
	public function preserves_existing_context_keys(): void {
		Functions\expect( 'get_stylesheet_directory' )->andReturn( '/theme' );
		Functions\expect( 'get_stylesheet_directory_uri' )->andReturn( 'https://example.com/theme' );
		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );

		$manager = $this->makeManager();
		$context = $manager->add_to_context( [ 'existing' => 'value' ] );

		$this->assertSame( 'value', $context['existing'] );
		$this->assertArrayHasKey( 'site', $context );
	}

	/**
	 * PHPUnit 9 does not have assertStringContainsString with that exact name,
	 * so use a local helper.
	 */
	private function assertStringContains( string $needle, string $haystack ): void {
		$this->assertTrue(
			str_contains( $haystack, $needle ),
			"Failed asserting that '$haystack' contains '$needle'."
		);
	}
}
