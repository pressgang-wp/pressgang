<?php

namespace PressGang\Tests\Unit;

use Brain\Monkey\Functions;
use PressGang\Bootstrap\Config;
use PressGang\Bootstrap\ConfigLoaderInterface;
use PressGang\Bootstrap\Loader;
use PressGang\PressGang;
use PressGang\ServiceProviders\ServiceProviderInterface;

class PressGangServiceProvidersTest extends TestCase {

	private function stubApplyFiltersPassThrough(): void {
		Functions\expect( 'apply_filters' )
			->zeroOrMoreTimes()
			->andReturnUsing( static function () {
				$args = func_get_args();
				return $args[1] ?? null;
			} );
	}

	protected function tear_down(): void {
		Config::clear_cache();
		parent::tear_down();
	}

	/** @test */
	public function boots_service_provider_classes_from_config(): void {
		StubAdditionalProvider::$booted = 0;

		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( [
			'service-providers' => [ StubAdditionalProvider::class ],
		] );
		Config::set_loader( $configLoader );

		$this->stubApplyFiltersPassThrough();

		$this->makePressGangForTest()->boot_service_providers_for_test();

		$this->assertSame( 1, StubAdditionalProvider::$booted );
	}

	/** @test */
	public function skips_invalid_provider_entries_silently(): void {
		StubAdditionalProvider::$booted = 0;

		$configLoader = $this->createStub( ConfigLoaderInterface::class );
		$configLoader->method( 'load' )->willReturn( [
			'service-providers' => [ 123, 'Not\\A\\Real\\Class', InvalidNoBootProvider::class, StubAdditionalProvider::class ],
		] );
		Config::set_loader( $configLoader );

		$this->stubApplyFiltersPassThrough();

		$this->makePressGangForTest()->boot_service_providers_for_test();

		// Only the valid ServiceProviderInterface implementation was booted.
		$this->assertSame( 1, StubAdditionalProvider::$booted );
	}

	private function makePressGangForTest(): PressGang {
		$loader = $this->createStub( Loader::class );

		return new class( $loader ) extends PressGang {
			public function boot_service_providers_for_test(): void {
				$this->boot_service_providers();
			}
		};
	}
}

class StubAdditionalProvider implements ServiceProviderInterface {
	public static int $booted = 0;

	public function boot(): void {
		self::$booted++;
	}
}

class InvalidNoBootProvider {
}
