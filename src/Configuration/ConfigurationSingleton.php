<?php

namespace PressGang\Configuration;

/**
 * Base singleton for all Configuration classes. Ensures each config handler is
 * instantiated only once and provides the shared $config property that is populated
 * by initialize() during the Loader boot sequence.
 */
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
	#[\Override]
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

	/**
	 * Clears all singleton instances. Intended for test isolation only.
	 */
	public static function reset_instances(): void {
		self::$instances = [];
	}

	abstract public function initialize( array $config ): void;
}
