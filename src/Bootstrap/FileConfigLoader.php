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
	private $configPath;

	/**
	 * Constructor for FileConfigLoader.
	 *
	 * Initializes a new instance of the FileConfigLoader with a specified path to configuration files.
	 * The path is relative to the root of the theme directories (both parent and child themes).
	 *
	 * @param string $configPath The relative path to the configuration files within the theme directories.
	 */
	public function __construct( string $configPath = '/config/' ) {
		$this->configPath = $configPath;
	}

	/**
	 * Load configuration settings from theme directories.
	 *
	 * This method loads configuration files from the parent theme directory first,
	 * and then from the child theme directory, allowing child theme settings to override parent theme settings.
	 *
	 * @return array The array of loaded settings.
	 */
	public function load(): array {
		$settings = [];

		// List of directories to load settings from
		$themePaths = [
			\get_template_directory() . $this->configPath, // Parent theme path
			\get_stylesheet_directory() . $this->configPath // Child theme path
		];

		// Apply a filter to allow modification of the config directories
		$themePaths = \apply_filters( 'pressgang_config_directories', $themePaths );

		// Loop through each directory and load settings
		foreach ( $themePaths as $path ) {
			$pathSettings = $this->load_from_directory( $path );
			$settings     = array_merge( $settings, $pathSettings );
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
	 * @param string $directoryPath The path to the directory from which to load the settings.
	 *
	 * @return array The array of settings loaded from the specified directory.
	 */
	private function load_from_directory( string $directoryPath ): array {
		$loadedSettings = [];

		if ( is_dir( $directoryPath ) ) {
			$configFiles = glob( $directoryPath . '*.php' );

			foreach ( $configFiles as $file ) {
				$settingKey = basename( $file, '.php' );
				$config     = require $file;
				if ( is_array( $config ) ) {
					$loadedSettings[ $settingKey ] = $config;
				}
			}
		}

		return $loadedSettings;
	}
}
