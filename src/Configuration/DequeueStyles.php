<?php

namespace PressGang\Configuration;

class DequeueStyles extends ConfigurationSingleton {

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
		\add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_styles' ] );
	}

	/**
	 * dequeue_styles
	 *
	 * @return void
	 */
	public function dequeue_styles(): void {
		foreach ( $this->config as $style ) {
			\wp_dequeue_style( $style );
		}
	}

}
