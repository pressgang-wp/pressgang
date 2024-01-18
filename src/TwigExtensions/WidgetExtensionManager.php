<?php

namespace PressGang\TwigExtensions;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * Class WidgetExtensionManager
 *
 * Implements TwigExtensionManagerInterface to add widget-related Twig functions to the Twig environment.
 * This class is responsible for dynamically adding functions for each registered and active sidebar (widget area) in WordPress.
 *
 * @package PressGang\TwigExtensions
 */
class WidgetExtensionManager implements TwigExtensionManagerInterface {

	use HasNoGlobals;

	/**
	 * This method iterates through all registered sidebars and adds a Twig function for each active sidebar.
	 * These functions allow templates to render widgets dynamically based on sidebar IDs.
	 *
	 * @param Environment $twig The Twig environment where the functions will be added.
	 */
	public function add_twig_functions( Environment $twig ): void {
		global $wp_registered_sidebars;

		foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {

			if ( \is_active_sidebar( $sidebar_id ) ) {
				$twig->addFunction( new TwigFunction( "widget_{$sidebar_id}", function () use ( $sidebar_id ) {
					return \Timber::get_widgets( $sidebar_id );
				} ) );
			}
		}

	}
}
