<?php

namespace PressGang\Controllers;

use PressGang\Util\ClassResolver;

/**
 * Factory that resolves and renders PressGang controllers. Given a WordPress template
 * filename, it infers the appropriate controller class, instantiates it, and calls render().
 * Used by PressGang::render() as the primary entry point from template files.
 *
 * Controllers are resolved child-theme-first: a child theme may override any
 * controller (including the PostController fallback) simply by declaring a class
 * of the same name under its own \Controllers namespace — no config required.
 *
 * Extend via: child theme namespace override, or the
 * pressgang_{controller}_template and pressgang_{controller}_context filters.
 */
class ControllerFactory {

	/**
	 * Creates a controller instance, passing the template only if provided.
	 *
	 * @param string $controller_class
	 * @param string|null $twig_template
	 *
	 * @return ControllerInterface
	 */
	public static function make( string $controller_class, ?string $twig_template = null ): ControllerInterface {
		// Use the splat operator to unpack filtered arguments (removes null values)
		// i.e. Honours controller default template args.
		return new $controller_class( ...array_filter( [ $twig_template ] ) );
	}

	/**
	 * Infers a controller FQCN from a WP template filename, falling back to PostController.
	 *
	 * @param string $template
	 *
	 * @return string Fully qualified controller class name.
	 */
	public static function infer_controller_class( string $template ): string {
		return self::resolve_controller_class( $template, \get_child_theme_namespace() );
	}

	/**
	 * Resolves a controller FQCN for a template against a given child namespace.
	 *
	 * Pure resolution logic (no WordPress dependencies) so it can be unit-tested
	 * with an explicit namespace. Tries the template-inferred controller child-first,
	 * then the parent framework, then the PostController fallback (also overridable
	 * by the child theme).
	 *
	 * @param string      $template        WP template filename or slug.
	 * @param string|null $child_namespace Active child theme namespace, or null.
	 *
	 * @return string Fully qualified controller class name.
	 */
	public static function resolve_controller_class( string $template, ?string $child_namespace ): string {
		$base = self::to_studly_case( basename( $template, '.php' ) ) . 'Controller';

		// Try the inferred controller, child theme first then the framework.
		$resolved = ClassResolver::resolve( $base, 'Controllers', $child_namespace );

		if ( $resolved !== null ) {
			return $resolved;
		}

		// Fall back to PostController, still honouring a child theme override of it.
		return ClassResolver::resolve( 'PostController', 'Controllers', $child_namespace )
			?? PostController::class;
	}

	/**
	 * Converts a template slug (e.g. 'single-product') to StudlyCase (e.g. 'SingleProduct').
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected static function to_studly_case( string $string ): string {
		$string = str_replace( [ '-', '_' ], ' ', $string );
		$string = ucwords( $string );

		return str_replace( ' ', '', $string );
	}

	/**
	 * Resolves a controller and renders it. Infers the controller from the template if not given.
	 *
	 * @param string|null $template
	 * @param string|null $controller
	 * @param string|null $twig
	 */
	public static function render( ?string $template = null, ?string $controller = null, ?string $twig = null ): void {
		$controller = $controller ?? self::infer_controller_class( $template );

		$controller = self::make( $controller, $twig );
		$controller->render();
	}
}
