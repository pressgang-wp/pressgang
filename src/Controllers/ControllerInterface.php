<?php

namespace PressGang\Controllers;

/**
 * Contract that all PressGang controllers must implement. Controllers are
 * template-scoped view models — they prepare context data and render a Twig template.
 */
interface ControllerInterface {

	/**
	 * @param string|null $template
	 */
	public function __construct( string|null $template = null );

	/**
	 * Renders the controller's template with its context.
	 */
	public function render(): void;
}
