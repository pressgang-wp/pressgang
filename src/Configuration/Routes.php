<?php

namespace PressGang\Configuration;

/**
 * Registers custom URL routes from config/routes.php using the Upstatement Routes
 * library. Each entry maps a route pattern to a template that Routes::load() will render.
 *
 * Why: provides declarative routing outside of WordPress's template hierarchy.
 * Extend via: child theme config override.
 *
 * @see https://timber.github.io/docs/v2/guides/routing/
 * @see https://github.com/Upstatement/routes
 */
class Routes extends ConfigurationSingleton {

	/**
	 * Initializes the Routes class with configuration data.
	 *
	 * Sets up the custom routes as defined in the configuration array, each pointing to a specific template.
	 *
	 * The Routes::map function is used to define a route.
	 * For each route, Routes::load is called with the specified template and parameters.
	 *
	 * @param array $config The configuration array for custom routes.
	 */
	#[\Override]
	public function initialize( array $config ): void {
		foreach ( $config as $route => $template ) {
			\Routes::map( $route, function ( $params ) use ( $template ) {
				\Routes::load( $template, $params, 200 );
			} );
		}
	}
}
