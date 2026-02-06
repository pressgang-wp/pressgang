<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;

/**
 * Trait HasNoFunctions
 *
 * Provides a default implementation for the add_twig_functions method in the TwigExtensionManagerInterface.
 * This default implementation is empty, indicating that no custom functions are added to the Twig environment.
 *
 * It's useful for extension manager classes that do not need to add any custom functions to Twig.
 *
 * @package PressGang\TwigExtensions
 */
trait HasNoFunctions {

	/**
	 * This is a default implementation and does not modify the Twig environment.
	 * It can be used in classes that implement TwigExtensionManagerInterface but do not require adding custom functions.
	 *
	 * @param Environment $twig The Twig environment.
	 */
	public function add_twig_functions( Environment $twig ): void {
		// Default implementation (empty) if not overridden
	}
}
