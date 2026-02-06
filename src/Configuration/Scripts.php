<?php

namespace PressGang\Configuration;

use PressGang\Helpers\ScriptLoader;

/**
 * Registers and enqueues JavaScript scripts from config/scripts.php. Delegates to
 * ScriptLoader for versioning, enqueueing, and async/defer attribute injection.
 *
 * Why: keeps script registration declarative with automatic cache-busting.
 * Extend via: child theme config override.
 */
class Scripts extends ConfigurationSingleton {

	/**
	 * Initializes the Scripts class with configuration data.
	 *
	 * Registers scripts based on provided configuration to be enqueued on the given hooks ( default = 'wp_enqueue_scripts' )
	 *
	 * @param array $config The configuration array for scripts.
	 */
	#[\Override]
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'handle_scripts' ] );
		\add_filter( 'script_loader_tag', [ ScriptLoader::class, 'add_script_attrs' ], 10, 3 );
	}

	/**
	 * Registers and enqueues scripts based on provided configurations.
	 *
	 * Iterates over each script configuration, sets default parameters, registers, and enqueues scripts.
	 *
	 * Handles additional attributes like 'async' and 'defer' for script loading.
	 *
	 * @see https://codex.wordpress.org/Function_Reference/wp_register_script
	 */
	public function handle_scripts(): void {
		foreach ( $this->config as $handle => $args ) {
			ScriptLoader::register_script( $handle, $args );
		}
	}

}
