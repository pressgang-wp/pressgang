<?php

namespace PressGang\ContextManagers;

/**
 * Interface ContextManagerInterface
 *
 * Defines the basic structure for a Context Manager in the PressGang framework.
 * A Context Manager class is responsible for modifying or adding data to the context array
 * that is passed to Timber templates.
 *
 * Implementing classes should provide their own logic for how the context is modified
 * or augmented, by defining the `add_to_context` method.
 *
 * @package PressGang\ContextManagers
 */
interface ContextManagerInterface {

	/**
	 * Modifies or adds to the context array.
	 *
	 * Implementing this method allows the class to inject data, modify existing values,
	 * or perform other operations on the context array used in rendering templates.
	 *
	 * @param array $context The context array that is passed to templates.
	 */
	public function add_to_context( $context );
}
