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
 * @see https://timber.github.io/docs/v2/guides/extending-twig/#adding-functionality-with-the-twig-environment-filter
 * @package PressGang\TwigExtensions
 */
class MetaDescriptionExtensionManager implements TwigExtensionManagerInterface {

	use HasNoGlobals;
	use HasNoFilters;

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
