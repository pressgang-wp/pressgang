<?php

namespace PressGang\Tests\Unit\Configuration;

use Brain\Monkey\Functions;
use PressGang\Configuration\Timber;
use PressGang\Tests\Unit\TestCase;

class TimberTest extends TestCase {
	protected function set_up(): void {
		parent::set_up();
		$this->resetSingletonInstances();
	}

	protected function tear_down(): void {
		$this->resetSingletonInstances();
		parent::tear_down();
	}

	/** @test */
	public function defaults_register_no_hooks(): void {
		Functions\expect( 'add_filter' )->never();
		Timber::get_instance()->initialize( [] );
		Timber::get_instance()->initialize( [ 'transform_acf_values' => false ] );
	}

	/** @test */
	public function transformation_can_be_enabled(): void {
		Functions\expect( 'add_filter' )->once()->with( 'timber/meta/transform_value', '__return_true' );
		Timber::get_instance()->initialize( [ 'transform_acf_values' => true ] );
	}

}
