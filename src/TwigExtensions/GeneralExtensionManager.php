<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * Class GeneralExtensionManager
 *
 * Implements TwigExtensionManagerInterface to add general-purpose Twig functions and global variables to the Twig environment.
 * This class provides basic utility functions and global variables commonly used in Twig templates.
 *
 * @see https://timber.github.io/docs/v2/guides/extending-twig/#adding-functionality-with-the-twig-environment-filter
 * @package PressGang\TwigExtensions
 */
class GeneralExtensionManager implements TwigExtensionManagerInterface {

	use HasNoFilters;

	/**
	 * Adds general utility functions to the Twig environment.
	 *
	 * Registers functions like 'get_search_query', 'get_option', and 'get_theme_mod' to the Twig environment,
	 * enabling their use within Twig templates for various purposes such as retrieving WordPress options and theme modifications.
	 *
	 * @param Environment $twig The Twig environment where the functions will be added.
	 */
	public function add_twig_functions( Environment $twig ): void {
		$twig->addFunction( new TwigFunction( 'get_search_query', 'get_search_query' ) );
		$twig->addFunction( new TwigFunction( 'get_option', 'get_option' ) );
		$twig->addFunction( new TwigFunction( 'get_theme_mod', 'get_theme_mod' ) );
	}

	/**
	 * Adds global variables to the Twig environment.
	 *
	 * Registers a global variable 'THEMENAME' to the Twig environment, which can be used in Twig templates often for namespacing translations.
	 * The value of 'THEMENAME' is determined by whether the constant THEMENAME is defined; if not, it defaults to 'pressgang'.
	 *
	 * @param Environment $twig The Twig environment where the global variable will be added.
	 */
	public function add_twig_globals( Environment $twig ) {
		$twig->addGlobal( 'THEMENAME', defined( 'THEMENAME' ) ? THEMENAME : 'pressgang' );
	}
}

