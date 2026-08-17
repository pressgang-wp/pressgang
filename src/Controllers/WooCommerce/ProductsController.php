<?php

namespace PressGang\Controllers\WooCommerce;

use PressGang\Controllers\PostsController;

/**
 * Class ProductsController
 *
 * Controller for handling WooCommerce product archive pages.
 * Extends the PostsController to add specific functionalities for WooCommerce product listings.
 *
 * @package PressGang
 */
class ProductsController extends PostsController {

	use HasShopSidebar;
	use HasProducts;

	/**
	 * ProductsController constructor.
	 *
	 * Initializes the controller for handling WooCommerce product archives,
	 * inferring the template from the current query when none is given.
	 *
	 * @param string|array<int, string>|null $template The template file(s) to use for rendering the product archive.
	 */
	public function __construct( string|array|null $template = null ) {
		parent::__construct( $template );
	}

	/**
	 * Builds the Twig template fallback chain for WooCommerce product archives.
	 *
	 * WooCommerce routes every product archive through the theme's
	 * `woocommerce.php`, which `WC_Template_Loader` places ahead of
	 * `taxonomy-{taxonomy}.php` in its candidate list. Themes shipping a
	 * `woocommerce.php` — as PressGang does — therefore lose WordPress'
	 * per-taxonomy template route entirely.
	 *
	 * That route is restored here in the Twig layer: a theme can add
	 * `woocommerce/taxonomy-product-brand.twig` and have it picked up for that
	 * taxonomy alone, exactly as `taxonomy-product_brand.php` would have been.
	 * Underscores become hyphens to match `PostsController::infer_template()`.
	 *
	 * Falls back to `woocommerce/archive-product.twig`, so themes without a
	 * taxonomy-specific template resolve exactly as they did before.
	 *
	 * @see https://developer.wordpress.org/themes/classic-themes/templates/taxonomy-templates/
	 *
	 * @return string|array<int, string>
	 */
	#[\Override]
	protected function infer_template(): string|array {

		if ( \is_product_taxonomy() ) {
			$taxonomy = \str_replace( '_', '-', \get_queried_object()->taxonomy );

			return [ "woocommerce/taxonomy-{$taxonomy}.twig", 'woocommerce/archive-product.twig' ];
		}

		return 'woocommerce/archive-product.twig';
	}

	/**
	 * Get the context for the template rendering.
	 *
	 * Extends the base get_context method from AbstractController, adding specific data
	 * like products, widget sidebar, and shop page display options to the context for rendering in the template.
	 *
	 * @return array The context array with additional data for the product archive page.
	 */
	#[\Override]
	protected function get_context(): array {
		parent::get_context();

		$this->context['products']          = $this->get_products();
		$this->context['shop_sidebar']      = $this->get_sidebar();
		$this->context['shop_page_display'] = \get_option( 'woocommerce_shop_page_display' );

		return $this->context;
	}
}
