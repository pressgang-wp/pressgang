<?php

namespace PressGang\Util;

/**
 * Resolves a relative class name to a fully-qualified class name, preferring a
 * child theme's namespace over the parent framework namespace.
 *
 * This is the single primitive behind PressGang's "override by convention"
 * mechanism: framework subsystems (snippets, controllers, …) ask for a class by
 * its short, sub-namespaced name (e.g. 'Hotjar', 'SingleProductController') and
 * a child theme can override it simply by declaring a class of the same name
 * under its own namespace — no config, no registration.
 *
 * Resolution order for a relative name + sub-namespace (e.g. 'Controllers'):
 *   1. If the name is already fully qualified under the parent or child root,
 *      return it when it exists (otherwise null).
 *   2. Child theme:  {child}\{sub}\{relative}
 *   3. Parent:       {parent}\{sub}\{relative}
 *
 * Unlike a runtime class_alias autoloader, resolution happens at the call site
 * and returns a concrete class-string, so static analysis, IDEs and stack traces
 * see the real class.
 */
class ClassResolver {

	/**
	 * Resolves a relative class name to a FQCN using the child-first ladder.
	 *
	 * @param string      $relative        Short or nested class name from config
	 *                                     (e.g. 'Hotjar', 'WooCommerce\ProductSwatch').
	 * @param string      $sub_namespace   Namespace segment under each root
	 *                                     (e.g. 'Snippets', 'Controllers'). Pass ''
	 *                                     to resolve directly under the roots.
	 * @param string|null $child_namespace The active child theme namespace, or null.
	 * @param string      $parent_namespace The framework root namespace.
	 *
	 * @return class-string|null Fully-qualified class name, or null if none exists.
	 */
	public static function resolve(
		string $relative,
		string $sub_namespace,
		?string $child_namespace,
		string $parent_namespace = 'PressGang'
	): ?string {
		$relative = ltrim( $relative, '\\' );
		$segment  = $sub_namespace !== '' ? trim( $sub_namespace, '\\' ) . '\\' : '';

		// Already fully qualified under a known root — take it as given.
		if (
			\str_starts_with( $relative, $parent_namespace . '\\' ) ||
			( $child_namespace && \str_starts_with( $relative, $child_namespace . '\\' ) )
		) {
			return \class_exists( $relative ) ? $relative : null;
		}

		// Child theme override takes precedence.
		if ( $child_namespace ) {
			$child_class = "{$child_namespace}\\{$segment}{$relative}";
			if ( \class_exists( $child_class ) ) {
				return $child_class;
			}
		}

		// Fall back to the parent framework.
		$parent_class = "{$parent_namespace}\\{$segment}{$relative}";

		return \class_exists( $parent_class ) ? $parent_class : null;
	}
}
