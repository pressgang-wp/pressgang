<?php

namespace PressGang\Tests\Unit\ServiceProviders;

use Brain\Monkey\Filters;
use PressGang\ServiceProviders\TemplateRoutingServiceProvider;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests that the opt-in routing provider registers the hierarchy observer
 * and the dispatcher.
 */
class TemplateRoutingServiceProviderTest extends TestCase {

	/** @test */
	public function boot_registers_hierarchy_and_dispatcher_filters(): void {
		Filters\expectAdded( 'taxonomy_template_hierarchy' )->once();
		Filters\expectAdded( 'template_include' )->once()->with( \Mockery::type( 'array' ), 90 );

		( new TemplateRoutingServiceProvider() )->boot();
	}
}
