<?php

namespace PressGang\Configuration;

use PressGang\Routes\RouteHandlerInterface;

/**
 * Registers custom URL routes from config/routes.php using the Upstatement Routes
 * library.
 *
 * Each entry maps a route pattern to either:
 *
 * - a template filename, loaded directly via Routes::load() with the matched
 *   route parameters available through the global $params, or
 * - a class implementing RouteHandlerInterface, instantiated and invoked with
 *   the matched route parameters — for routes that need logic (parameter
 *   resolution, query args) before loading a template.
 *
 *     return [
 *         'archive/:year'        => 'archive-year.php',
 *         'hit/:hit/news/'       => \MyTheme\Routes\HitNewsRoute::class,
 *     ];
 *
 * Why: provides declarative routing outside of WordPress's template hierarchy.
 * Extend via: child theme config override.
 *
 * @see https://timber.github.io/docs/v2/guides/routing/
 * @see https://github.com/Upstatement/routes
 */
class Routes extends ConfigurationSingleton {

	/**
	 * Maps each configured route to its template or handler class.
	 *
	 * @param array $config The configuration array for custom routes.
	 */
	#[\Override]
	public function initialize( array $config ): void {
		foreach ( $config as $route => $handler ) {
			\Routes::map( $route, $this->make_callback( $handler ) );
		}
	}

	/**
	 * Builds the route callback for a template filename or handler class.
	 *
	 * @param string $handler Template filename, or RouteHandlerInterface class name.
	 *
	 * @return callable
	 */
	protected function make_callback( string $handler ): callable {

		if ( is_subclass_of( $handler, RouteHandlerInterface::class ) ) {
			return static function ( $params ) use ( $handler ): void {
				( new $handler() )->handle( $params ?: [] );
			};
		}

		return static function ( $params ) use ( $handler ): void {
			\Routes::load( $handler, $params );
		};
	}
}
