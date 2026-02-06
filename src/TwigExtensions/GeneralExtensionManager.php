<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * Registers general-purpose Twig functions that expose common WordPress settings
 * (get_option, get_theme_mod, get_search_query) and the THEMENAME global for
 * use in translation filters.
 *
 * @see https://timber.github.io/docs/v2/guides/extending-twig/
 */
class GeneralExtensionManager implements TwigExtensionManagerInterface {

	use HasNoFilters;

	/**
	 * @param Environment $twig
	 */
	#[\Override]
	public function add_twig_functions( Environment $twig ): void {
		$twig->addFunction( new TwigFunction( 'get_search_query', 'get_search_query' ) );
		$twig->addFunction( new TwigFunction( 'get_option', 'get_option' ) );
		$twig->addFunction( new TwigFunction( 'get_theme_mod', 'get_theme_mod' ) );
	}

	/**
	 * Exposes the THEMENAME constant as a Twig global for translation text domains.
	 *
	 * @param Environment $twig
	 */
	#[\Override]
	public function add_twig_globals( Environment $twig ): void {
		$twig->addGlobal( 'THEMENAME', defined( 'THEMENAME' ) ? THEMENAME : 'pressgang' );
	}
}

