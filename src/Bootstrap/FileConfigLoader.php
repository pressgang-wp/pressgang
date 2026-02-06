<?php

namespace PressGang\Bootstrap;

/**
 * Default ConfigLoaderInterface implementation. Loads PHP config files from the
 * parent and child theme config/ directories (child overrides parent), and caches
 * the merged result via wp_cache or transients.
 */
class FileConfigLoader implements ConfigLoaderInterface {

	const CACHE_KEY = 'pressgang_config_settings';
	const CACHE_GROUP = 'config_settings';

	/**
	 * @param string $config_path Relative path to config directory within themes.
	 */
	public function __construct( private readonly string $config_path = '/config/' ) {
	}

	/**
	 * Returns cached settings or loads from disk and caches them.
	 *
	 * @return array<string, mixed>
	 */
	public function load(): array {
		$settings = $this->get_cached_settings();

		if ( $settings === false ) {
			$settings = $this->load_config();
			$this->set_cached_settings( $settings );
		}

		return $settings;
	}

	/**
	 * @return mixed Settings from cache or false if not cached.
	 */
	private function get_cached_settings(): mixed {
		if ( defined( 'PRESSGANG_CONFIG_CACHE_SECONDS' ) && PRESSGANG_CONFIG_CACHE_SECONDS ) {
			return \get_transient( self::CACHE_KEY );
		} else {
			return \wp_cache_get( self::CACHE_KEY, self::CACHE_GROUP );
		}
	}

	/**
	 * @param array<string, mixed> $settings
	 */
	private function set_cached_settings( array $settings ): void {
		if ( defined( 'PRESSGANG_CONFIG_CACHE_SECONDS' ) && PRESSGANG_CONFIG_CACHE_SECONDS ) {
			\set_transient( self::CACHE_KEY, $settings, PRESSGANG_CONFIG_CACHE_SECONDS );
		} else {
			\wp_cache_set( self::CACHE_KEY, $settings, self::CACHE_GROUP );
		}
	}

	/**
	 * Merges config from parent then child theme directories.
	 *
	 * @return array<string, mixed>
	 */
	private function load_config(): array {
		$settings = [];

		// List of directories to load settings from
		$theme_paths = [
			\get_template_directory() . $this->config_path, // Parent theme path
			\get_stylesheet_directory() . $this->config_path // Child theme path
		];

		// Apply a filter to allow modification of the config directories
		$theme_paths = \apply_filters( 'pressgang_config_directories', $theme_paths );

		// Loop through each directory and load settings
		foreach ( $theme_paths as $path ) {
			$path_settings = $this->load_from_directory( $path );
			$settings      = array_merge( $settings, $path_settings );
		}

		return $settings;
	}

	/**
	 * Requires each *.php file in a directory and indexes by filename (minus extension).
	 *
	 * @param string $directory_path
	 *
	 * @return array<string, mixed>
	 */
	private function load_from_directory( string $directory_path ): array {
		$loaded_settings = [];

		if ( is_dir( $directory_path ) ) {
			$config_files = glob( $directory_path . '*.php' );

			foreach ( $config_files as $file ) {
				$setting_key = basename( $file, '.php' );
				$config      = require $file;
				if ( is_array( $config ) ) {
					$loaded_settings[ $setting_key ] = $config;
				}
			}
		}

		return $loaded_settings;
	}
}
