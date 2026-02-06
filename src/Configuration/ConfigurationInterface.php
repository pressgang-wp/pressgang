<?php

namespace PressGang\Configuration;

/**
 * Contract for config-driven registration classes. Each implementation maps to a
 * config file (e.g. config/sidebars.php → Sidebars) and is instantiated as a
 * singleton by the Loader during boot.
 */
interface ConfigurationInterface {

	/**
	 * @param array<string|int, mixed> $config
	 */
	public function initialize( array $config ): void;

	/**
	 * @return static
	 */
	public static function get_instance(): static;
}
