<?php

namespace PressGang\Configuration;

/**
 * Registers WordPress actions from config/actions.php, where each entry is a
 * hook name => callback pair passed directly to add_action().
 *
 * Why: keeps action registration declarative and centralised.
 * Extend via: child theme config override.
 */
class Actions extends ConfigurationSingleton {

	/**
	 * @param array<string, callable> $config
	 */
	public function initialize( array $config ): void {
		foreach ( $config as $key => $args ) {
			\add_action( $key, $args );
		}
	}
}
