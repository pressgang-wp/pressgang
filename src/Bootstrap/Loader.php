<?php

namespace PressGang\Bootstrap;

use PressGang\Configuration\ConfigurationInterface;
use Symfony\Component\String\u;

/**
 * Class Loader
 *
 * Responsible for dynamically loading and initializing theme components based on configuration.
 * It also includes additional files such as 'shortcodes' and 'widgets'.
 *
 * @package PressGang\Bootstrap
 */
class Loader {

	/**
	 * @var ConfigLoaderInterface
	 */
	private ConfigLoaderInterface $configLoader;

	/**
	 * @var array
	 */
	private array $include_folders = [
		'shortcodes',
		'widgets',
	];

	/**
	 * Loader constructor.
	 *
	 * @param ConfigLoaderInterface $configLoader
	 */
	public function __construct( ConfigLoaderInterface $configLoader ) {
		$this->configLoader = $configLoader;
	}

	/**
	 * Initialize the loading of components and inclusion of files.
	 */
	public function initialize(): void {
		$this->load_components();
		$this->include_files();
	}

	/**
	 * Load and initialize components based on the theme's configuration.
	 *
	 * Iterates through configuration, dynamically loads each component,
	 * and initializes it if the component class implements the ConfigurationInterface.
	 */
	protected function load_components(): void {
		Config::set_loader( $this->configLoader );

		foreach ( Config::get() as $key => $config ) {
			$class_name = $this->config_key_to_configuration_class( $key );
			if ( class_exists( $class_name ) && in_array( ConfigurationInterface::class, class_implements( $class_name ) ) ) {
				$instance = $class_name::get_instance();
				if ( method_exists( $instance, 'initialize' ) ) {
					$instance->initialize( $config );
				}
			}
		}
	}

	/**
	 * Converts a configuration key to a class name in the Configuration namespace.
	 *
	 * @param string $key The configuration key.
	 *
	 * @return string The fully qualified class name.
	 */
	protected function config_key_to_configuration_class( string $key ): string {
		$studlyCase = u( $key )->camel()->title( true );

		return "PressGang\\Configuration\\$studlyCase";
	}

	/**
	 * Includes additional files based on the theme's configuration.
	 *
	 * This typically includes files from the 'shortcodes', and 'widgets' directories.
	 */
	protected function include_files(): void {
		foreach ( $this->include_folders as $folder ) {
			if ( $files = Config::get( $folder ) ) {
				foreach ( $files as $file ) {
					$this->include_file( $folder, $file );
				}
			}
		}
	}

	/**
	 * Includes a specific file from the specified folder.
	 *
	 * @param string $folder
	 * @param string $file
	 */
	protected function include_file( string $folder, string $file ): void {
		$directories = \apply_filters( "pressgang_include_directories", [
			\get_stylesheet_directory(),
			\get_template_directory(),
		], $folder, $file );

		foreach ( $directories as $directory ) {
			$filePath = "{$directory}/{$folder}/{$file}.php";
			if ( file_exists( $filePath ) ) {
				require_once $filePath;
				break;
			}
		}
	}
}
