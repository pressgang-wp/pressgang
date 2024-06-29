<?php

namespace PressGang\Configuration;

/**
 * Class Routes
 *
 * Manages custom routing in WordPress using Timber's routing capabilities see https://github.com/Upstatement/routes.
 * This class uses a configuration array to define custom routes and the corresponding templates.
 * It extends ConfigurationSingleton to ensure that it is only instantiated once.
 *
 * @see https://timber.github.io/docs/v2/guides/routing/
 * @see https://github.com/Upstatement/routes
 * @package PressGang
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
	public function initialize( array $config ): void {
		foreach ( $config as $route => $template ) {
			\Routes::map( $route, function ( $params ) use ( $template ) {
				\Routes::load( $template, $params, 200 );
			} );
		}
	}
}
