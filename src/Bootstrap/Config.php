<?php

namespace PressGang\Bootstrap;

/**
 * Class Config
 *
 * Provides a central access point to retrieve theme configuration settings.
 */
class Config {

	/**
	 * @var array|null The array of configuration settings.
	 */
	private static ?array $settings = null;

	/**
	 * @var ConfigLoaderInterface|null The loader responsible for fetching configuration settings.
	 */
	private static ?ConfigLoaderInterface $loader = null;

	/**
	 * Sets the configuration loader.
	 *
	 * @param ConfigLoaderInterface $loader The loader to be used for loading configuration settings.
	 */
	public static function set_loader( ConfigLoaderInterface $loader ): void {
		self::$loader = $loader;
	}

	/**
	 * Retrieves a configuration setting.
	 *
	 * If a key is provided, returns the specific setting associated with that key,
	 * otherwise returns all settings. If the key does not exist, returns the default value.
	 *
	 * @param string|null $key The key of the configuration setting to retrieve.
	 * @param array $default The default value to return if the setting key is not found.
	 *
	 * @return mixed The configuration setting value or the default value.
	 */
	public static function get( ?string $key = null, mixed $default = [] ): mixed {
		if ( self::$settings === null && self::$loader ) {
			self::$settings = \apply_filters( 'pressgang_get_config', self::$loader->load() );
		}

		if ( $key !== null ) {
			return self::$settings[ $key ] ?? $default;
		}

		return self::$settings;
	}

	/**
	 * Clears the cached settings.
	 *
	 * Useful if you need to force a reload of the settings, for example, after changing configuration files.
	 */
	public static function clear_cache(): void {
		self::$settings = null;
	}
}
