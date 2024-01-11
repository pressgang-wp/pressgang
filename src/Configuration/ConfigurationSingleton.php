<?php

namespace PressGang\Configuration;

abstract class ConfigurationSingleton implements ConfigurationInterface {
	private static $instances = [];

	/**
	 * Configuration array for the class loaded from the config files.
	 *
	 * @var array
	 */
	protected $config = [];

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
	public static function get_instance() {
		$class = static::class;
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new static();
		}

		return self::$instances[ $class ];
	}

	// Prevent cloning and unserialization
	private function __clone() {
	}

	// Implement the 'initialize' method from ConfigurationInterface
	abstract public function initialize( array $config );
}
