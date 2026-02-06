<?php

namespace PressGang\Bootstrap;

/**
 * Static facade for accessing merged parent/child theme configuration. Lazy-loads
 * settings from the ConfigLoaderInterface on first access and exposes them via
 * Config::get(). Settings are filterable through 'pressgang_get_config'.
 */
class Config {

	/** @var array<string, mixed>|null */
	private static ?array $settings = null;

	/** @var ConfigLoaderInterface|null */
	private static ?ConfigLoaderInterface $loader = null;

	/**
	 * @param ConfigLoaderInterface $loader
	 */
	public static function set_loader( ConfigLoaderInterface $loader ): void {
		self::$loader = $loader;
	}

	/**
	 * Returns a single config key or all settings. Lazy-loads from the loader on first call.
	 *
	 * @param string|null $key
	 * @param mixed $default
	 *
	 * @return mixed
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
	 * Forces a reload of settings on the next get() call.
	 */
	public static function clear_cache(): void {
		self::$settings = null;
	}
}
