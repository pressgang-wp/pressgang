<?php

namespace PressGang\TwigExtensions;

use PressGang\SEO\MetaDescriptionService;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * Registers the meta_description() Twig function, which delegates to
 * MetaDescriptionService to generate SEO-friendly descriptions for the current page.
 *
 * @see https://timber.github.io/docs/v2/guides/extending-twig/
 */
class MetaDescriptionExtensionManager implements TwigExtensionManagerInterface {

	use HasNoGlobals;
	use HasNoFilters;

	/**
	 * Adds a meta description function to the Twig environment.
	 *
	 * @param Environment $twig The Twig environment where the function will be added.
	 */
	#[\Override]
	public function add_twig_functions( Environment $twig ): void {
		$twig->addFunction( new TwigFunction( 'meta_description', [
			MetaDescriptionService::class,
			'get_meta_description'
		] ) );
	}
}
