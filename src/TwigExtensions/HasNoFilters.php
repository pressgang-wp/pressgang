<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;

/**
 * Trait HasNoFilters
 *
 * Provides a default implementation for the add_twig_filters method in the TwigExtensionManagerInterface.
 * This default implementation is empty, indicating that no custom filters are added to the Twig environment.
 *
 * It's useful for extension manager classes that do not need to add any filters to Twig.
 *
 * @package PressGang\TwigExtensions
 */
trait HasNoFilters {

	/**
	 * This is a default implementation and does not modify the Twig environment.
	 * It can be used in classes that implement TwigExtensionManagerInterface but do not require adding custom filters.
	 *
	 * @param Environment $twig The Twig environment.
	 */
	public function add_twig_filters( Environment $twig ) {
		// Default implementation (empty) if not overridden
	}
}
