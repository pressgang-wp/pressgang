<?php

namespace PressGang\Configuration;

/**
 * Class Actions
 *
 * Manages the registration of WordPress actions based on a provided configuration array.
 * This class extends ConfigurationSingleton to ensure that it is only instantiated once,
 * and utilizes a configuration array to add WordPress actions dynamically.
 *
 * @package PressGang
 */
class Actions extends ConfigurationSingleton {

	/**
	 * Initializes the Actions class with a configuration array.
	 *
	 * This method iterates over the provided configuration array and registers each action
	 * with WordPress. The configuration array should contain key-value pairs where the key
	 * is the action hook name and the value is the callback function or method to be executed.
	 *
	 * Example configuration array:
	 * [
	 *     'init' => 'my_init_function',                             // Global function
	 *     'wp_head' => ['MyClass', 'my_wp_head_static_method'],     // Static class method
	 *     'template_redirect' => [$myObject, 'redirect_method'],    // Instance method
	 *     ...
	 * ]
	 *
	 * @param array $config The configuration array for actions.
	 */
	public function initialize( array $config ): void {
		foreach ( $config as $key => $args ) {
			\add_action( $key, $args );
		}
	}
}
