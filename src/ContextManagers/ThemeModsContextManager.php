<?php

namespace PressGang\ContextManagers;

/**
 * Adds all WordPress theme_mods to the context's 'theme' object, making Customizer
 * settings available in templates. Each value is filterable via pressgang_theme_mod_{key}.
 */
class ThemeModsContextManager implements ContextManagerInterface {

	/**
	 * @param array<string, mixed> $context
	 *
	 * @return array<string, mixed>
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
