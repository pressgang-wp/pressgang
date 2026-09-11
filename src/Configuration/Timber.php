<?php

namespace PressGang\Configuration;

/**
 * Registers the metadata policy from config/timber.php without replacing Timber's bridge.
 * Transformation is opt-in so existing child themes retain their value contracts.
 * Twig environment settings remain handled by TimberServiceProvider.
 */
class Timber extends ConfigurationSingleton {
	/**
	 * @param array<array-key, mixed> $config
	 * @return void
	 */
	#[\Override]
	public function initialize( array $config ): void {
		if ( ( $config['transform_acf_values'] ?? false ) === true ) {
			\add_filter( 'timber/meta/transform_value', '__return_true' );
		}

	}
}
