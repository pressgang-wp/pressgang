<?php

namespace PressGang\Configuration;

/**
 * Dequeues CSS stylesheets listed in config/dequeue-styles.php. Used to remove
 * unwanted default or plugin stylesheets from the frontend.
 *
 * Why: keeps stylesheet removal declarative and out of functions.php.
 * Extend via: child theme config override.
 */
class DequeueStyles extends ConfigurationSingleton {

	/**
	 * @param array<string, mixed> $config
	 */
	#[\Override]
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_styles' ] );
	}

	/**
	 * Dequeues each configured style handle.
	 */
	public function dequeue_styles(): void {
		foreach ( $this->config as $style ) {
			\wp_dequeue_style( $style );
		}
	}

}
