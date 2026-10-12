<?php

namespace PressGang\Configuration;

/** Declares resource preloads through WordPress's native preload API. */
class Preloads extends ConfigurationSingleton {
	/** @var array<array-key, array<string, string>> */
	private array $preloads = [];

	/** @param array<array-key, array<string, string>> $config */
	#[\Override]
	public function initialize( array $config ): void {
		$this->preloads = $config;
		\add_filter( 'wp_preload_resources', [ $this, 'resources' ] );
	}

	/**
	 * Preserve resources registered by WordPress, plugins, and other filters.
	 * WordPress owns attribute handling, escaping, and duplicate suppression.
	 *
	 * @param array<array-key, array<string, string>> $resources Existing resources.
	 * @return array<array-key, array<string, string>>
	 */
	public function resources( array $resources ): array {
		return array_merge( $resources, $this->preloads );
	}
}
