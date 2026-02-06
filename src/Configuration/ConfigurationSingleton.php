<?php

namespace PressGang\Configuration;

abstract class ConfigurationSingleton implements ConfigurationInterface {
	private static array $instances = [];

	/**
	 * Configuration array for the class loaded from the config files.
	 *
	 * @var array
	 */
	protected array $config = [];

	/**
	 * Protected constructor to prevent creating a new instance.
	 */
	protected function __construct() {
	}

	/**
	 * Gets the Singleton instance of this class.
	 *
	 * @return static The Singleton instance.
	 */
	public static function get_instance(): static {
		$class = static::class;
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new static();
		}

		return self::$instances[ $class ];
	}

	// Prevent cloning and un-serialization
	private function __clone() {
	}

	abstract public function initialize( array $config ): void;
}
