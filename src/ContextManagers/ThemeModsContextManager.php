<?php

namespace PressGang\ContextManagers;

/**
 * Class ThemeModsContextManager
 *
 * Manages the integration of WordPress theme modification settings (theme mods) into the Timber context.
 * This class retrieves all theme mods using WordPress functions and adds them to the Timber context,
 * allowing these settings to be accessible within the theme's templates.
 *
 * Implements the ContextManagerInterface to ensure consistent handling of context data in the PressGang framework.
 *
 * @package PressGang\ContextManagers
 */
class ThemeModsContextManager implements ContextManagerInterface {

	/**
	 * Adds WordPress theme modification settings (theme mods) to the Timber context.
	 *
	 * Retrieves all theme mods using the get_theme_mods function and adds each mod
	 * to the Timber context in the 'theme' object. This allows theme mods to be easily accessed and used within
	 * the theme's Twig templates. The value of each mod can be filtered using the
	 * 'pressgang_theme_mod_[key]' filter, allowing further customization.
	 *
	 * @param array $context The Timber context array that is passed to templates.
	 *
	 * @return array
	 */
	public function add_to_context( array $context ): array {

		if ( $theme_mods = \get_theme_mods() ) {
			foreach ( $theme_mods as $key => $value ) {
				$context['theme']->$key = \apply_filters( "pressgang_theme_mod_$key", $value );
			}
		}

		return $context;
	}

}
