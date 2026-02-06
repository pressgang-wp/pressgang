<?php

namespace PressGang\Configuration;

interface ConfigurationInterface {

	/**
	 * Initialize configuration settings, typically from config files.
	 */
	public function initialize( array $config ): void;

	/**
	 * Get a Singleton instance of the Configuration class.
	 *
	 * @return static
	 */
	public static function get_instance(): static;
}
