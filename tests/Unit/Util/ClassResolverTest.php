<?php

// Fixture classes exercising the child-first resolution ladder. Declared under
// throwaway roots so the resolver's $parent_namespace can be pointed at them
// without depending on real framework or child-theme classes.
namespace PressGang\Tests\Fixtures\Parent\Controllers {
	class WidgetController {}
	class PostController {}
}

namespace PressGang\Tests\Fixtures\Parent\Snippets\WooCommerce {
	class ProductSwatch {}
}

namespace PressGang\Tests\Fixtures\Child\Controllers {
	class WidgetController {}
}

namespace PressGang\Tests\Unit\Util {

	use PressGang\Tests\Unit\TestCase;
	use PressGang\Util\ClassResolver;

	/**
	 * Tests the child-first → parent-fallback class resolution ladder that backs
	 * PressGang's "override by convention" mechanism.
	 */
	class ClassResolverTest extends TestCase {

		private const PARENT = 'PressGang\\Tests\\Fixtures\\Parent';
		private const CHILD  = 'PressGang\\Tests\\Fixtures\\Child';

		/** @test */
		public function prefers_child_class_when_both_exist(): void {
			$resolved = ClassResolver::resolve( 'WidgetController', 'Controllers', self::CHILD, self::PARENT );

			$this->assertSame( self::CHILD . '\\Controllers\\WidgetController', $resolved );
		}

		/** @test */
		public function falls_back_to_parent_when_child_does_not_define_it(): void {
			// 'PostController' exists only under the parent fixture root.
			$resolved = ClassResolver::resolve( 'PostController', 'Controllers', self::CHILD, self::PARENT );

			$this->assertSame( self::PARENT . '\\Controllers\\PostController', $resolved );
		}

		/** @test */
		public function uses_parent_only_when_no_child_namespace(): void {
			$resolved = ClassResolver::resolve( 'WidgetController', 'Controllers', null, self::PARENT );

			$this->assertSame( self::PARENT . '\\Controllers\\WidgetController', $resolved );
		}

		/** @test */
		public function returns_null_when_neither_defines_it(): void {
			$resolved = ClassResolver::resolve( 'MissingController', 'Controllers', self::CHILD, self::PARENT );

			$this->assertNull( $resolved );
		}

		/** @test */
		public function resolves_nested_sub_namespaces_under_parent(): void {
			$resolved = ClassResolver::resolve( 'WooCommerce\\ProductSwatch', 'Snippets', self::CHILD, self::PARENT );

			$this->assertSame( self::PARENT . '\\Snippets\\WooCommerce\\ProductSwatch', $resolved );
		}

		/** @test */
		public function returns_fully_qualified_parent_class_as_given(): void {
			$fqcn = self::PARENT . '\\Controllers\\PostController';

			$this->assertSame( $fqcn, ClassResolver::resolve( $fqcn, 'Controllers', self::CHILD, self::PARENT ) );
		}

		/** @test */
		public function returns_fully_qualified_child_class_as_given(): void {
			$fqcn = self::CHILD . '\\Controllers\\WidgetController';

			$this->assertSame( $fqcn, ClassResolver::resolve( $fqcn, 'Controllers', self::CHILD, self::PARENT ) );
		}

		/** @test */
		public function returns_null_for_non_existent_fully_qualified_class(): void {
			$fqcn = self::PARENT . '\\Controllers\\GhostController';

			$this->assertNull( ClassResolver::resolve( $fqcn, 'Controllers', self::CHILD, self::PARENT ) );
		}

		/** @test */
		public function resolves_directly_under_roots_with_empty_sub_namespace(): void {
			// Empty sub-namespace: resolve straight under the root (no segment).
			$resolved = ClassResolver::resolve(
				'Controllers\\WidgetController',
				'',
				self::CHILD,
				self::PARENT
			);

			$this->assertSame( self::CHILD . '\\Controllers\\WidgetController', $resolved );
		}

		/** @test */
		public function tolerates_leading_backslash_in_relative_name(): void {
			$resolved = ClassResolver::resolve( '\\WidgetController', 'Controllers', self::CHILD, self::PARENT );

			$this->assertSame( self::CHILD . '\\Controllers\\WidgetController', $resolved );
		}
	}
}
