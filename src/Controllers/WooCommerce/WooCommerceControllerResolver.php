<?php

namespace PressGang\Controllers\WooCommerce;

use PressGang\Util\ClassResolver;

/**
 * Resolves the appropriate WooCommerce controller based on the current query.
 */
class WooCommerceControllerResolver {

	/**
	 * Determines the controller class for the current WooCommerce page.
	 *
	 * @return string The fully qualified controller class name.
	 */
	public static function resolve(): string {
		if ( \is_singular( 'product' ) ) {
			return self::resolve_controller( 'WooCommerce\ProductController' );
		}

		if ( \is_product_category() ) {
			return self::resolve_controller(
				match ( \get_option( 'woocommerce_category_archive_display' ) ) {
					'subcategories' => 'WooCommerce\ProductCategoryController',
					'both' => 'WooCommerce\ProductCategoriesAndProductsController',
					default => 'WooCommerce\ProductsController',
				}
			);
		}

		if ( \is_shop() ) {
			return self::resolve_controller(
				match ( \get_option( 'woocommerce_shop_page_display' ) ) {
					'subcategories' => 'WooCommerce\ProductCategoriesController',
					'both' => 'WooCommerce\ProductCategoriesAndProductsController',
					default => 'WooCommerce\ProductsController',
				}
			);
		}

		return self::resolve_product_taxonomy_controller()
			?? self::resolve_controller( 'WooCommerce\ProductsController' );
	}

	/**
	 * Resolve a controller named after the queried product taxonomy.
	 *
	 * Mirrors the `taxonomy-{taxonomy}` => `{Taxonomy}Controller` convention
	 * used elsewhere in PressGang, so a custom product taxonomy can take a
	 * dedicated controller without a theme having to override the shared
	 * `ProductsController` — which handles the shop and every other product
	 * archive besides.
	 *
	 * A `product_promotion` taxonomy resolves to `ProductPromotionController`.
	 * Returns null when no such class exists, leaving the caller to fall back.
	 *
	 * @return string|null Fully qualified controller class name, or null.
	 */
	protected static function resolve_product_taxonomy_controller(): ?string {
		if ( ! \is_product_taxonomy() ) {
			return null;
		}

		$term = \get_queried_object();

		if ( ! $term instanceof \WP_Term ) {
			return null;
		}

		return self::resolve_taxonomy_controller_class( $term->taxonomy, \get_child_theme_namespace() );
	}

	/**
	 * Resolve a taxonomy-named controller against a specific child namespace.
	 *
	 * @param string      $taxonomy        Registered taxonomy name, e.g. `product_promotion`.
	 * @param string|null $child_namespace Active child theme namespace, or null.
	 *
	 * @return string|null Fully qualified controller class name, or null.
	 */
	public static function resolve_taxonomy_controller_class( string $taxonomy, ?string $child_namespace ): ?string {
		$studly = \str_replace( ' ', '', \ucwords( \str_replace( [ '-', '_' ], ' ', $taxonomy ) ) );

		if ( '' === $studly ) {
			return null;
		}

		return ClassResolver::resolve( "WooCommerce\\{$studly}Controller", 'Controllers', $child_namespace );
	}

	/**
	 * Resolve a WooCommerce controller child-theme-first, matching PressGang's
	 * standard controller override convention.
	 *
	 * @param string $relative Relative controller name under the Controllers namespace.
	 * @return string Fully qualified controller class name.
	 */
	protected static function resolve_controller( string $relative ): string {
		return self::resolve_controller_class( $relative, \get_child_theme_namespace() );
	}

	/**
	 * Resolve a WooCommerce controller against a specific child namespace.
	 *
	 * @param string      $relative        Relative controller name under the Controllers namespace.
	 * @param string|null $child_namespace Active child theme namespace, or null.
	 * @return string Fully qualified controller class name.
	 */
	public static function resolve_controller_class( string $relative, ?string $child_namespace ): string {
		return ClassResolver::resolve( $relative, 'Controllers', $child_namespace )
			?? ProductsController::class;
	}
}
