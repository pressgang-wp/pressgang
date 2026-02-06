<?php

namespace PressGang\Tests\Unit\Configuration;

use Brain\Monkey\Functions;
use PressGang\Configuration\Actions;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests the Actions configuration class: registers WordPress action hooks
 * from config key => callback pairs.
 */
class ActionsTest extends TestCase {

	protected function set_up(): void {
		parent::set_up();
		$this->resetSingletonInstances();
	}

	protected function tear_down(): void {
		$this->resetSingletonInstances();
		parent::tear_down();
	}

	/** @test */
	public function initialize_registers_each_action(): void {
		$callback1 = function () {};
		$callback2 = function () {};

		Functions\expect( 'add_action' )
			->once()
			->with( 'init', $callback1 );

		Functions\expect( 'add_action' )
			->once()
			->with( 'wp_head', $callback2 );

		$actions = Actions::get_instance();
		$actions->initialize( [
			'init'    => $callback1,
			'wp_head' => $callback2,
		] );
	}

	/** @test */
	public function empty_config_registers_no_actions(): void {
		Functions\expect( 'add_action' )->never();

		$actions = Actions::get_instance();
		$actions->initialize( [] );
	}

	/** @test */
	public function get_instance_returns_singleton(): void {
		$a = Actions::get_instance();
		$b = Actions::get_instance();

		$this->assertSame( $a, $b );
	}
}
