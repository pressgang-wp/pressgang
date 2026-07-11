<?php

namespace PressGang\Controllers;

/**
 * Contract that all PressGang controllers must implement. Controllers are
 * template-scoped view models — they prepare context data and render a Twig template.
 */
interface ControllerInterface {

	/**
	 * The framework constructs controllers generically via
	 * ControllerFactory::make() with a `?string` template — so `?string` is the
	 * true minimal construction contract every controller must honour. Widening
	 * here would make the interface over-promise a capability generic dispatch
	 * never supplies. AbstractController legitimately accepts more
	 * (`string|array|null` for a fallback chain) — an implementation exceeding
	 * its interface, which is fine.
	 *
	 * @param string|null $template
	 */
	public function __construct( string|null $template = null );

	/**
	 * Renders the controller's template with its context.
	 */
	public function render(): void;
}
