<?php

namespace PressGang\Controllers;

/**
 * Interface ControllerInterface
 *
 * Defines the basic structure for controllers in the PressGang theme.
 * It ensures that all implementing controllers adhere to a consistent method signature for construction and rendering.
 *
 * @package PressGang\Controllers
 */
interface ControllerInterface {

	/**
	 * Constructor for the controller.
	 *
	 * Initializes a new instance of a controller with an optional Twig template.
	 *
	 * @param string|null $template Optional Twig template file name to be used with the controller.
	 */
	public function __construct( $template = null );

	/**
	 * Render the view associated with this controller.
	 *
	 * This method should handle the logic required to prepare and display the view for this controller.
	 */
	public function render();
}
