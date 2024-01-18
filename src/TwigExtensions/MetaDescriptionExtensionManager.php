<?php

namespace PressGang\TwigExtensions;

use PressGang\SEO\MetaDescriptionService;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * Class MetaDescriptionExtensionManager
 *
 * Implements the TwigExtensionManagerInterface to add meta description related functions to the Twig environment.
 *
 * @package PressGang\TwigExtensions
 */
class MetaDescriptionExtensionManager implements TwigExtensionManagerInterface {

	use HasNoGlobals;

	/**
	 * Adds a meta description function to the Twig environment.
	 *
	 * @param Environment $twig The Twig environment where the function will be added.
	 */
	public function add_twig_functions( Environment $twig ): void {
		$twig->addFunction( new TwigFunction( 'meta_description', [
			MetaDescriptionService::class,
			'get_meta_description'
		] ) );
	}
}
