<?php

namespace PressGang\Controllers;

/**
 * Class ControllerFactory
 *
 * A factory class for instantiating and managing controllers in the PressGang theme.
 * It provides methods to create controller instances and render views.
 *
 * @package PressGang\Controllers
 */
class ControllerFactory {

	/**
	 * Create an instance of a controller class with an optional Twig template.
	 *
	 * @param string $controller_class The class name of the controller to instantiate.
	 * @param string|null $twig_template Optional Twig template to be used with the controller.
	 *
	 * @return object An instance of the specified controller class.
	 */
	public static function make( string $controller_class, string $twig_template = null ): object {
		return new $controller_class( $twig_template );
	}

	/**
	 * Infer the controller class name based on a given template file name.
	 *
	 * @param string $template The filename of the template.
	 *
	 * @return object An instance of the inferred controller class.
	 */
	public static function infer_controller_class( string $template ) {
		$template         = basename( $template, '.php' );
		$controller_class = self::to_filename( $template ) . 'Controller';

		if ( class_exists( $controller_class ) ) {
			return new $controller_class();
		}

		// Fallback to a default controller if needed
		return new PostController();
	}

	/**
	 * Convert a string to a valid filename format for controllers.
	 *
	 * @param string $string The string to convert.
	 *
	 * @return string The formatted string suitable for controller filenames.
	 */
	protected static function to_filename( string $string ): string {
		$string = str_replace( [ '-', '_' ], ' ', $string );
		$string = ucwords( $string );
		$string = str_replace( ' ', '', $string );
		$string .= 'Controller';

		return $string;
	}

	/**
	 * Render a view using a specified controller and Twig template.
	 *
	 * @param array $params An associative array of parameters for rendering.
	 *                      Possible keys: 'template', 'controller_class', 'twig_template'.
	 */
	public static function render( array $params = [] ) {
		$template         = $params['template'] ?? null;
		$controller_class = $params['controller_class'] ?? self::infer_controller_class( $template );
		$twig_template    = $params['twig_template'] ?? null;

		$controller = self::make( $controller_class, $twig_template );
		$controller->render();
	}
}

