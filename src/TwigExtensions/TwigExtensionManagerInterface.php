<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;

/**
 * Interface TwigExtensionManagerInterface
 *
 * Defines the methods that Twig extension manager classes must implement.
 * These methods are responsible for adding custom functions and global variables to the Twig environment.
 *
 * @see https://timber.github.io/docs/v2/guides/extending-twig/#adding-functionality-with-the-twig-environment-filter
 * @package PressGang\TwigExtensions
 */
interface TwigExtensionManagerInterface {

	/**
	 * Adds custom Twig functions to the Twig environment.
	 *
	 * Implement this method to register new functions that can be used in Twig templates.
	 *
	 * @param Environment $twig The Twig environment to which functions will be added.
	 */
	public function add_twig_functions( Environment $twig );

	/**
	 * Adds custom filters to the Twig environment.
	 *
	 * Implement this method to register new custom filter that can be accessed in Twig templates.
	 *
	 * @param Environment $twig The Twig environment to which filters will be added.
	 */
	public function add_twig_filters( Environment $twig );

	/**
	 * Adds global variables to the Twig environment.
	 *
	 * Implement this method to register new global variables that can be accessed in Twig templates.
	 *
	 * @param Environment $twig The Twig environment to which global variables will be added.
	 */
	public function add_twig_globals( Environment $twig );
}
