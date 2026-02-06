<?php

namespace PressGang\Controllers;

/**
 * Factory that resolves and renders PressGang controllers. Given a WordPress template
 * filename, it infers the appropriate controller class, instantiates it, and calls render().
 * Used by PressGang::render() as the primary entry point from template files.
 *
 * Extend via: pressgang_{controller}_template and pressgang_{controller}_context filters.
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
	 * Infers a controller class from a WP template filename, falling back to PostController.
	 *
	 * @param string $template
	 *
	 * @return ControllerInterface
	 */
	public static function infer_controller_class( string $template ): ControllerInterface {
		$template         = basename( $template, '.php' );
		$controller_class = self::to_filename( $template ) . 'Controller';

		if ( class_exists( $controller_class ) ) {
			return new $controller_class();
		}

		// Fallback to a default controller if needed
		return new PostController();
	}

	/**
	 * Converts a template slug (e.g. 'single-product') to a StudlyCase controller class name.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected static function to_filename( string $string ): string {
		$string = str_replace( [ '-', '_' ], ' ', $string );
		$string = ucwords( $string );
		$string = str_replace( ' ', '', $string );
		$string .= 'Controller';

		return $string;
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
