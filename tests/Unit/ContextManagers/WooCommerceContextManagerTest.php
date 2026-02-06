<?php

namespace PressGang\Tests\Unit\ContextManagers;

use Brain\Monkey\Functions;
use PressGang\ContextManagers\WooCommerceContextManager;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests WooCommerceContextManager: WooCommerce availability check, cached links,
 * link building on cache miss, and cart count retrieval.
 *
 * Uses anonymous subclasses to override is_woocommerce_active(), build_links(),
 * and get_cart_contents_count() to avoid dependence on the WooCommerce plugin.
 */
class WooCommerceContextManagerTest extends TestCase {

	/**
	 * Creates a manager that reports WooCommerce as inactive.
	 *
	 * @return WooCommerceContextManager
	 */
	private function makeInactiveManager(): WooCommerceContextManager {
		return new class extends WooCommerceContextManager {
			protected function is_woocommerce_active(): bool {
				return false;
			}
		};
	}

	/**
	 * Creates a manager that reports WooCommerce as active, with stubbed cart count.
	 *
	 * @param int $cartCount
	 *
	 * @return WooCommerceContextManager
	 */
	private function makeActiveManager( int $cartCount = 0 ): WooCommerceContextManager {
		return new class( $cartCount ) extends WooCommerceContextManager {
			public function __construct( private readonly int $cartCount ) {
			}

			protected function is_woocommerce_active(): bool {
				return true;
			}

			protected function get_cart_contents_count(): int {
				return $this->cartCount;
			}
		};
	}

	/** @test */
	public function returns_context_unchanged_when_woocommerce_inactive(): void {
		$original = [ 'site' => 'test' ];

		$manager = $this->makeInactiveManager();
		$context = $manager->add_to_context( $original );

		$this->assertSame( $original, $context );
		$this->assertArrayNotHasKey( 'my_account_link', $context );
	}

	/** @test */
	public function returns_cached_links_on_cache_hit(): void {
		$cached = [
			'my_account_link' => '/my-account',
			'logout_link'     => '/logout',
			'cart_link'       => '/cart',
			'checkout_link'   => '/checkout',
		];

		Functions\expect( 'wp_cache_get' )
			->with( 'pressgang_wc_links' )
			->andReturn( $cached );

		$manager = $this->makeActiveManager();
		$context = $manager->add_to_context( [] );

		$this->assertSame( '/my-account', $context['my_account_link'] );
		$this->assertSame( '/logout', $context['logout_link'] );
		$this->assertSame( '/cart', $context['cart_link'] );
		$this->assertSame( '/checkout', $context['checkout_link'] );
		$this->assertSame( 0, $context['cart_contents_count'] );
	}

	/** @test */
	public function builds_and_caches_links_on_cache_miss(): void {
		Functions\expect( 'wp_cache_get' )
			->with( 'pressgang_wc_links' )
			->andReturn( false );

		Functions\expect( 'get_option' )
			->with( 'woocommerce_myaccount_page_id' )
			->andReturn( 42 );

		Functions\expect( 'get_permalink' )->with( 42 )->andReturn( '/my-account' );
		Functions\expect( 'wp_logout_url' )->with( '/my-account' )->andReturn( '/logout?from=my-account' );

		// wc_get_cart_url and wc_get_checkout_url use function_exists checks
		Functions\expect( 'wc_get_cart_url' )->andReturn( '/cart' );
		Functions\expect( 'wc_get_checkout_url' )->andReturn( '/checkout' );

		Functions\expect( 'wp_cache_set' )
			->once()
			->with( 'pressgang_wc_links', \Mockery::on( function ( $links ) {
				return $links['my_account_link'] === '/my-account'
					&& $links['cart_link'] === '/cart';
			} ) );

		$manager = $this->makeActiveManager( 3 );
		$context = $manager->add_to_context( [] );

		$this->assertSame( '/my-account', $context['my_account_link'] );
		$this->assertSame( '/logout?from=my-account', $context['logout_link'] );
		$this->assertSame( '/cart', $context['cart_link'] );
		$this->assertSame( '/checkout', $context['checkout_link'] );
		$this->assertSame( 3, $context['cart_contents_count'] );
	}

	/** @test */
	public function cart_count_is_always_fresh(): void {
		$cached = [
			'my_account_link' => '/my-account',
			'logout_link'     => '/logout',
			'cart_link'       => '/cart',
			'checkout_link'   => '/checkout',
		];

		Functions\expect( 'wp_cache_get' )->andReturn( $cached );

		$manager = $this->makeActiveManager( 7 );
		$context = $manager->add_to_context( [] );

		$this->assertSame( 7, $context['cart_contents_count'] );
	}

	/** @test */
	public function preserves_existing_context_keys(): void {
		$cached = [
			'my_account_link' => '/my-account',
			'logout_link'     => '/logout',
			'cart_link'       => '/cart',
			'checkout_link'   => '/checkout',
		];

		Functions\expect( 'wp_cache_get' )->andReturn( $cached );

		$manager = $this->makeActiveManager();
		$context = $manager->add_to_context( [ 'site' => 'preserved', 'theme' => 'exists' ] );

		$this->assertSame( 'preserved', $context['site'] );
		$this->assertSame( 'exists', $context['theme'] );
		$this->assertArrayHasKey( 'my_account_link', $context );
	}
}
