<?php

namespace Acme\Theme\Controllers\WooCommerce {
	class ProductsController extends \PressGang\Controllers\WooCommerce\ProductsController {}
	class ProductController extends \PressGang\Controllers\WooCommerce\ProductController {}
}

namespace PressGang\Tests\Unit\Controllers {

	use PressGang\Controllers\WooCommerce\ProductController;
	use PressGang\Controllers\WooCommerce\ProductsController;
	use PressGang\Controllers\WooCommerce\WooCommerceControllerResolver;
	use PressGang\Tests\Unit\TestCase;

	class WooCommerceControllerResolverTest extends TestCase {

		/** @test */
		public function resolves_child_product_controller(): void {
			$this->assertSame(
				'Acme\\Theme\\Controllers\\WooCommerce\\ProductController',
				WooCommerceControllerResolver::resolve_controller_class( 'WooCommerce\ProductController', 'Acme\\Theme' )
			);
		}

		/** @test */
		public function resolves_child_products_controller(): void {
			$this->assertSame(
				'Acme\\Theme\\Controllers\\WooCommerce\\ProductsController',
				WooCommerceControllerResolver::resolve_controller_class( 'WooCommerce\ProductsController', 'Acme\\Theme' )
			);
		}

		/** @test */
		public function falls_back_to_framework_products_controller_without_child_namespace(): void {
			$this->assertSame(
				ProductsController::class,
				WooCommerceControllerResolver::resolve_controller_class( 'WooCommerce\ProductsController', null )
			);
		}

		/** @test */
		public function falls_back_to_framework_single_product_controller_without_child_namespace(): void {
			$this->assertSame(
				ProductController::class,
				WooCommerceControllerResolver::resolve_controller_class( 'WooCommerce\ProductController', null )
			);
		}
	}
}
