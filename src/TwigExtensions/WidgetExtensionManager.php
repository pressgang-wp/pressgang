<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * Registers a widget_{sidebar_id}() Twig function for each registered sidebar,
 * allowing templates to render sidebar widgets dynamically by location.
 *
 * @see https://timber.github.io/docs/v2/guides/extending-twig/
 */
class WidgetExtensionManager implements TwigExtensionManagerInterface {

	use HasNoGlobals;
	use HasNoFilters;

	/**
	 * This method iterates through all registered sidebars and adds a Twig function for each active sidebar.
	 * These functions allow templates to render widgets dynamically based on sidebar IDs.
	 *
	 * @param Environment $twig The Twig environment where the functions will be added.
	 */
	#[\Override]
	public function add_twig_functions( Environment $twig ): void {
		global $wp_registered_sidebars;

		foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
			$twig->addFunction( new TwigFunction( "widget_{$sidebar_id}", function () use ( $sidebar_id ) {
				return \Timber::get_widgets( $sidebar_id );
			} ) );
		}

	}
}
