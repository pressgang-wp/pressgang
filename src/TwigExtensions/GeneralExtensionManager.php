<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;
use Twig\TwigFunction;

class GeneralExtensionManager implements TwigExtensionManagerInterface {

	public function add_twig_functions( Environment $twig ) {
		$twig->addFunction( new TwigFunction( 'get_search_query', 'get_search_query' ) );
		$twig->addFunction( new TwigFunction( 'get_option', 'get_option' ) );
		$twig->addFunction( new TwigFunction( 'get_theme_mod', 'get_theme_mod' ) );
	}

	public function add_twig_globals( Environment $twig ) {
		$twig->addGlobal( 'THEMENAME', defined( 'THEMENAME' ) ? THEMENAME : 'pressgang' );

	}
}

