<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;

/**
 * Trait HasNoGlobals
 *
 * Provides a default implementation for the add_twig_globals method in the TwigExtensionManagerInterface.
 * This default implementation is empty, indicating that no global variables are added to the Twig environment.
 *
 * It's useful for extension manager classes that do not need to add any global variables to Twig.
 *
 * @package PressGang\TwigExtensions
 */
trait HasNoGlobals {

	/**
	 * This is a default implementation and does not modify the Twig environment.
	 * It can be used in classes that implement TwigExtensionManagerInterface but do not require adding global variables.
	 *
	 * @param Environment $twig The Twig environment.
	 */
	public function add_twig_globals( Environment $twig ) {
		// Default implementation (empty) if not overridden
	}
}
