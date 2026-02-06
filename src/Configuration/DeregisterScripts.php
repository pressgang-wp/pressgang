<?php

namespace PressGang\Configuration;

/**
 * Deregisters JavaScript scripts listed in config/deregister-scripts.php on the
 * frontend only. Commonly used to remove default WordPress scripts like jQuery.
 *
 * Why: keeps script removal declarative and scoped to the frontend.
 * Extend via: child theme config override.
 */
class DeregisterScripts extends ConfigurationSingleton {

	/**
	 * @param array<string, mixed> $config
	 */
	#[\Override]
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'deregister_scripts' ] );
	}

	/**
	 * Deregisters each configured script on the frontend only.
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
