<?php

namespace PressGang\Routes;

/**
 * Contract for custom route handlers registered via config/routes.php.
 *
 * Use a handler class (instead of a plain template string) when the route
 * needs logic — resolving route parameters to content, building query args —
 * before loading a template. The handler receives the matched route
 * parameters and is responsible for calling \Routes::load() (or otherwise
 * responding).
 *
 * @see \PressGang\Configuration\Routes
 * @see https://github.com/Upstatement/routes
 */
interface RouteHandlerInterface {

	/**
	 * Handles a matched route.
	 *
	 * @param array $params Route parameters matched from the URL pattern.
	 *
	 * @return void
	 */
	public function handle( array $params ): void;
}
