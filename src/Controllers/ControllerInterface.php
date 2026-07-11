<?php

namespace PressGang\Controllers;

/**
 * Contract that all PressGang controllers must implement. Controllers are
 * template-scoped view models — they prepare context data and render a Twig template.
 */
interface ControllerInterface {

	/**
	 * Deliberately the narrowest contract (`?string`): implementations — and
	 * PHP's contravariance rules — may accept more (AbstractController also
	 * takes a fallback chain array), but widening the interface itself would
	 * fatal every existing child-theme controller declared with `?string`.
	 *
	 * @param string|null $template
	 */
	public function __construct( string|null $template = null );

	/**
	 * Renders the controller's template with its context.
	 */
	public function render(): void;
}
