<?php

namespace PressGang\Core;

use PressGang\Classes\Helper;

/**
 * Class Loader
 *
 * @package PressGang
 */
class Loader {

	/**
	 * init
	 *
	 * Require the necessary files
	 *
	 */
	public function __construct() {

		$this->auto_loader();

		foreach ( Config::get() as $key => $config ) {

			$className = Helper::hyphenated_to_camel( $key, true );

			// prepend namespace
			$className = "PressGang\\Core\\{$className}";

			if ( class_exists( $className ) ) {
				// Instantiate the class, you can pass $config to the constructor if needed
				new $className( $config );
			}
		}

		// load inc, shortcodes, widgets files
		foreach ( array( 'inc', 'shortcodes', 'widgets' ) as $folder ) {
			if ( $config = Config::get( $folder ) ) {
				foreach ( $config as $file ) {
					$inc = preg_match( '/.php/', $file ) ? "{$folder}/{$file}" : "{$folder}/{$file}.php";
					locate_template( $inc, true, true );
				}
			}
		}
	}

	/**
	 * Register a PSR-4 compliant autoloader for framework classes.
	 *
	 * This autoloader function is designed to automatically include class files
	 * based on their namespace and class name, adhering to the PSR-4 standard.
	 * It uses the spl_autoload_register function to handle the class loading.
	 *
	 * In PSR-4, the fully qualified class name is used to locate the corresponding file.
	 * This method assumes that the namespace root ('PressGang') corresponds to the
	 * base directory defined within this method. It then maps the rest of the fully
	 * qualified class name to a path relative to this base directory.
	 *
	 * The namespace separators are converted to directory separators and concatenated
	 * with the root directory to form the full path to the class file. The '.php' extension
	 * is appended to complete the file name. This autoloader only includes the file if
	 * it exists to avoid any unnecessary errors.
	 *
	 * Usage:
	 * Register this autoloader method using spl_autoload_register in your application's
	 * bootstrap process. Once registered, it will be automatically called by PHP
	 * whenever a class is used that hasn't been included yet.
	 *
	 * Example:
	 * spl_autoload_register([new Loader(), 'auto_loader']);
	 */
	public function auto_loader() {
		spl_autoload_register( function ( $class ) {
			// Base directory for the namespace prefix
			$base_dir = __DIR__ . '/../';

			// Does the class use the namespace prefix?
			$len = strlen( 'PressGang\\' );
			if ( strncmp( 'PressGang\\', $class, $len ) !== 0 ) {
				// no, move to the next registered autoloader
				return;
			}

			// Get the relative class name
			$relative_class = substr( $class, $len );

			// Replace the namespace prefix with the base directory, replace namespace
			// separators with directory separators in the relative class name, append
			// with .php
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			// If the file exists, require it
			if ( file_exists( $file ) ) {
				require $file;
			}
		} );
	}
}

new Loader();
