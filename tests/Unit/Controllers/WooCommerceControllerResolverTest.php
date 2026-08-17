<?php

namespace Acme\Theme\Controllers\WooCommerce {
	class ProductsController extends \PressGang\Controllers\WooCommerce\ProductsController {}
	class ProductController extends \PressGang\Controllers\WooCommerce\ProductController {}
	class ProductPromotionController extends \PressGang\Controllers\WooCommerce\ProductsController {}
	class ClearanceController extends \PressGang\Controllers\WooCommerce\ProductsController {}
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

		/** @test */
		public function resolves_a_child_controller_named_after_a_product_taxonomy(): void {
			$this->assertSame(
				'Acme\\Theme\\Controllers\\WooCommerce\\ProductPromotionController',
				WooCommerceControllerResolver::resolve_taxonomy_controller_class( 'product_promotion', 'Acme\\Theme' )
			);
		}

		/** @test */
		public function taxonomy_controller_resolution_returns_null_when_none_is_defined(): void {
			$this->assertNull(
				WooCommerceControllerResolver::resolve_taxonomy_controller_class( 'product_widget', 'Acme\\Theme' )
			);
		}

		/** @test */
		public function taxonomy_controller_resolution_returns_null_without_a_child_namespace(): void {
			$this->assertNull(
				WooCommerceControllerResolver::resolve_taxonomy_controller_class( 'product_promotion', null )
			);
		}


		/** @test */
		public function taxonomy_controller_resolution_accepts_the_unprefixed_name(): void {
			$this->assertSame(
				'Acme\\Theme\\Controllers\\WooCommerce\\ClearanceController',
				WooCommerceControllerResolver::resolve_taxonomy_controller_class( 'product_clearance', 'Acme\\Theme' )
			);
		}

		/** @test */
		public function the_prefixed_name_wins_when_both_exist(): void {
			$this->assertSame(
				'Acme\\Theme\\Controllers\\WooCommerce\\ProductPromotionController',
				WooCommerceControllerResolver::resolve_taxonomy_controller_class( 'product_promotion', 'Acme\\Theme' )
			);
		}

	}
}
