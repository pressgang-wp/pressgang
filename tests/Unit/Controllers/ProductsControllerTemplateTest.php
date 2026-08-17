<?php

namespace PressGang\Tests\Unit\Controllers {

	use Brain\Monkey\Functions;
	use PressGang\Controllers\WooCommerce\ProductsController;
	use PressGang\Tests\Unit\TestCase;

	/**
	 * Exposes the protected template inference without booting the controller,
	 * whose constructor reaches for the global query.
	 */
	class InspectableProductsController extends ProductsController {

		public function __construct( string|array|null $template = null ) {
			// Intentionally does not call parent::__construct().
		}

		/**
		 * @return string|array<int, string>
		 */
		public function inferred_template(): string|array {
			return $this->infer_template();
		}
	}

	/**
	 * Product archives are routed through the theme's `woocommerce.php`, which
	 * WooCommerce prioritises over `taxonomy-{taxonomy}.php`. These cover the
	 * Twig-layer replacement for that lost per-taxonomy route.
	 */
	class ProductsControllerTemplateTest extends TestCase {

		private function queue_product_taxonomy( string $taxonomy ): void {
			$term           = new \stdClass();
			$term->taxonomy = $taxonomy;

			Functions\when( 'is_product_taxonomy' )->justReturn( true );
			Functions\when( 'get_queried_object' )->justReturn( $term );
		}

		/** @test */
		public function shop_archives_keep_the_default_template(): void {
			Functions\when( 'is_product_taxonomy' )->justReturn( false );

			$this->assertSame(
				'woocommerce/archive-product.twig',
				( new InspectableProductsController() )->inferred_template()
			);
		}

		/** @test */
		public function product_taxonomies_gain_a_taxonomy_specific_candidate(): void {
			$this->queue_product_taxonomy( 'product_brand' );

			$this->assertSame(
				[ 'woocommerce/taxonomy-product-brand.twig', 'woocommerce/archive-product.twig' ],
				( new InspectableProductsController() )->inferred_template()
			);
		}

		/** @test */
		public function underscores_in_taxonomy_names_become_hyphens(): void {
			$this->queue_product_taxonomy( 'product_promotion' );

			$this->assertSame(
				'woocommerce/taxonomy-product-promotion.twig',
				( new InspectableProductsController() )->inferred_template()[0]
			);
		}

		/** @test */
		public function the_default_template_remains_the_final_fallback(): void {
			$this->queue_product_taxonomy( 'product_cat' );

			$candidates = ( new InspectableProductsController() )->inferred_template();

			$this->assertSame( 'woocommerce/archive-product.twig', \end( $candidates ) );
		}
	}
}
