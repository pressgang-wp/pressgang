<?php

namespace PressGang\Bootstrap;

/**
 * FileConfigLoader class
 *
 * Implements a configuration loader that reads settings from PHP files located in theme directories.
 * It is designed to support hierarchical settings where child theme settings can override parent theme settings.
 * Each PHP file in the specified configuration directory should return an associative array of settings.
 */
class FileConfigLoader implements ConfigLoaderInterface {
	private string $config_path;

	/**
	 * Constructor for FileConfigLoader.
	 *
	 * Initializes a new instance of the FileConfigLoader with a specified path to configuration files.
	 * The path is relative to the root of the theme directories (both parent and child themes).
	 *
	 * @param string $config_path The relative path to the configuration files within the theme directories.
	 */
	public function __construct( string $config_path = '/config/' ) {
		$this->config_path = $config_path;
	}

	/**
	 * Loads configuration settings utilizing object caching.
	 *
	 * This method attempts to retrieve the configuration settings from the WordPress object cache.
	 *
	 * If the settings are not available in the cache, it loads them from the file system,
	 * specifically from the configuration files located under the parent and child theme directories.
	 * Once the settings are loaded from the files, they are stored in the cache for future requests.
	 *
	 * @return array The array of loaded settings, either from the cache or from the file system if not cached.
	 */
	public function load(): array {
		$cache_key   = 'pressgang_config_settings';
		$cache_group = 'config_settings';

		$settings = \wp_cache_get( $cache_key, $cache_group );

		if ( $settings === false ) {
			$settings = $this->load_configurations();
			\wp_cache_set( $cache_key, $settings, $cache_group );
		}

		return $settings;
	}

	/**
	 * Load configuration settings from theme directories.
	 *
	 * This method loads configuration files from the parent theme directory first,
	 * and then from the child theme directory, allowing child theme settings to override parent theme settings.
	 *
	 * @return array The array of loaded settings.
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
	 * Load configuration settings from a specific directory.
	 *
	 * Loads PHP files located in the configuration path of the parent and child theme directories and interprets each file's name
	 * (minus the '.php' extension) as the setting key.
	 *
	 * Files in the child theme directory will override those in the parent theme directory if they have the same name.
	 *
	 * Each configuration file should return an associative array of settings.
	 *
	 * @param string $directory_path The path to the directory from which to load the settings.
	 *
	 * @return array The array of settings loaded from the specified directory.
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
