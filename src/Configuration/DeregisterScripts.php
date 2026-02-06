<?php

namespace PressGang\Configuration;

/**
 * Class Scripts
 *
 * Manages the  de-registration of JavaScript scripts in WordPress.
 *
 * @package PressGang
 */
class DeregisterScripts extends ConfigurationSingleton {

	/**
	 * initialize
	 *
	 * Adds scripts from the settings file to be enqueued on the given hooks (default = 'wp_enqueue_scripts')
	 *
	 * See - https://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
	 *
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'deregister_scripts' ] );
	}

	/**
	 * deregister_scripts
	 *
	 * Can be used for unloading jQuery etc.
	 *
	 */
	public function deregister_scripts(): void {
		if ( ! \is_admin() ) {

			foreach ( $this->config as $key => $args ) {

				\add_action( 'wp_enqueue_scripts', function () use ( $key ) {
					\wp_deregister_script( $key );
				}, 0 );

			}

		}
	}

}
