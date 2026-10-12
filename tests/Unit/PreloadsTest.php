<?php

namespace PressGang\Tests\Unit;

use Brain\Monkey\Functions;
use PressGang\Configuration\ConfigurationSingleton;
use PressGang\Configuration\Preloads;

class PreloadsTest extends TestCase {
	protected function tear_down(): void {
		ConfigurationSingleton::reset_instances();
		parent::tear_down();
	}

	/** @test */
	public function preserves_existing_preloads_and_passes_through_resource_attributes(): void {
		$preloads = Preloads::get_instance();
		Functions\expect( 'add_filter' )->once()->with( 'wp_preload_resources', [ $preloads, 'resources' ] );
		$font = [ 'href' => '/fonts/text.woff2', 'as' => 'font', 'type' => 'font/woff2', 'crossorigin' => 'anonymous' ];
		$image = [ 'href' => '/hero.webp', 'as' => 'image', 'imagesrcset' => '/hero.webp 1200w', 'imagesizes' => '100vw', 'fetchpriority' => 'high', 'media' => '(min-width: 600px)' ];
		$existing = [ [ 'href' => '/plugin.js', 'as' => 'script' ] ];
		$preloads->initialize( [ $font, $image ] );
		$this->assertSame( array_merge( $existing, [ $font, $image ] ), $preloads->resources( $existing ) );
	}

	/** @test */
	public function empty_config_leaves_existing_resources_unchanged(): void {
		$preloads = Preloads::get_instance();
		Functions\expect( 'add_filter' )->once();
		$preloads->initialize( [] );
		$existing = [ [ 'href' => '/plugin.js', 'as' => 'script' ] ];
		$this->assertSame( $existing, $preloads->resources( $existing ) );
	}
}
