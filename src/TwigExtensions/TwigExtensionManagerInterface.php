<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;

/**
 * Contract for Twig extension managers. Implementations register custom Twig
 * functions, filters, and globals, and are listed in config/twig-extensions.php.
 *
 * @see https://timber.github.io/docs/v2/guides/extending-twig/
 */
interface TwigExtensionManagerInterface {

	/**
	 * @param Environment $twig
	 */
	public function add_twig_functions( Environment $twig ): void;

	/**
	 * @param Environment $twig
	 */
	public function add_twig_filters( Environment $twig ): void;

	/**
	 * @param Environment $twig
	 */
	public function add_twig_globals( Environment $twig ): void;
}
