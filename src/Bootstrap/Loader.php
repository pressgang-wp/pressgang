<?php

namespace PressGang\Bootstrap;

use PressGang\Configuration\ConfigurationInterface;
use function Symfony\Component\String\u;

/**
 * Class Loader
 *
 * Responsible for dynamically loading and initializing theme components based on configuration.
 * It also includes additional files such as 'inc', 'shortcodes', and 'widgets'.
 *
 * @package PressGang\Bootstrap
 */
class Loader {

	/**
	 * Folders to include additional files from.
	 */
	const INCLUDE_FOLDERS = [ 'inc', 'shortcodes', 'widgets' ];

	/**
	 * Loader constructor.
	 *
	 * Initializes the loading of components and inclusion of files.
	 */
	public function __construct( ConfigLoaderInterface $config_loader ) {
		$this->load_components( $config_loader );
		$this->include_files();
	}

	/**
	 * Load and initialize components based on the theme's configuration.
	 *
	 * Iterates through configuration, dynamically loads each component,
	 * and initializes it if the component class implements the ConfigurationInterface.
	 */
	protected function load_components( ConfigLoaderInterface $config_loader ) {

		Config::set_loader( $config_loader );

		foreach ( Config::get() as $key => $config ) {
			$className = $this->config_key_to_configuration_class( $key );
			if ( class_exists( $className ) && class_implements( $className, ConfigurationInterface::class ) ) {
				$instance = $className::get_instance();
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
	protected function config_key_to_configuration_class( $key ) {
		$studlyCase = u( $key )->camel()->title( true );

		return "PressGang\\Configuration\\" . $studlyCase;
	}

	/**
	 * Includes additional files based on the theme's configuration.
	 *
	 * This typically includes files from 'inc', 'shortcodes', and 'widgets' directories.
	 */
	protected function include_files() {
		foreach ( self::INCLUDE_FOLDERS as $folder ) {
			if ( $files = Config::get( $folder ) ) {
				foreach ( $files as $file ) {
					$filePath = \get_template_directory() . "/{$folder}/{$file}.php";
					if ( file_exists( $filePath ) ) {
						require_once $filePath;
					}
				}
			}
		}
	}
}
