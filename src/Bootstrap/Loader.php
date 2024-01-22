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
	const SNIPPETS_CONFIG = 'snippets';
	const SHORTCODES_FOLDER = 'shortcodes';
	const WIDGETS_FOLDER = 'widgets';

	/**
	 * Loader constructor.
	 *
	 * Initializes the loading of components and inclusion of files.
	 */
	public function __construct( ConfigLoaderInterface $config_loader ) {
		$this->load_components( $config_loader );
		$this->include_snippets();
		$this->include_files();
	}

	/**
	 * Load and initialize components based on the theme's configuration.
	 *
	 * Iterates through configuration, dynamically loads each component,
	 * and initializes it if the component class implements the ConfigurationInterface.
	 */
	protected function load_components( ConfigLoaderInterface $config_loader ): void {

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
	protected function config_key_to_configuration_class( $key ): string {
		$studlyCase = u( $key )->camel()->title( true );

		return "PressGang\\Configuration\\" . $studlyCase;
	}

	/**
	 * Includes additional files based on the theme's configuration.
	 *
	 * This typically includes files from the 'shortcodes', and 'widgets' directories.
	 */
	protected function include_files(): void {

		$includes = [
			self::SHORTCODES_FOLDER,
			self::WIDGETS_FOLDER,
		];

		foreach ( $includes as $folder ) {
			if ( $files = Config::get( $folder ) ) {
				foreach ( $files as $file ) {
					// Define the directories to search in
					$directories = [
						\get_stylesheet_directory(),
						\get_template_directory()
					];

					$directories = \apply_filters( "pressgang_include_directories", $directories, $folder, $file );

					foreach ( $directories as $directory ) {
						$filePath = "{$directory}/{$folder}/{$file}.php";
						if ( file_exists( $filePath ) ) {
							require_once $filePath;
							break; // Break out of the loop once the file is found and included
						}
					}
				}
			}
		}
	}

	/**
	 * Includes and initializes snippet classes based on the configuration file (e.g., snippets.php).
	 *
	 * This method reads the specified snippets configuration and dynamically loads and initializes
	 * the snippet classes. The configuration can specify each snippet by either a fully qualified
	 * class name or just a simple class name.
	 *
	 * If a fully qualified namespace is provided in the configuration, it uses that namespace directly.
	 * Otherwise, it attempts to guess the namespace: it first looks for the snippet in the child theme's
	 * \Snippets namespace and then in the PressGang\Snippets namespace.
	 *
	 * The configuration for each snippet should include the class name as the key (which can be a simple
	 * class name or a fully qualified class name) and an array of arguments as the value, which will be
	 * passed to the class's constructor.
	 *
	 * The snippets configuration is expected to be in the format:
	 * [
	 *     'Fully\\Qualified\\Namespace\\SnippetClassName' => ['arg1' => 'value1', 'arg2' => 'value2'],
	 *     'SimpleSnippetClassName' => ['arg3' => 'value3', 'arg4' => 'value4'],
	 *     ...
	 * ]
	 *
	 * @return void
	 */
	protected function include_snippets(): void {

		$child_theme_namespace = $this->get_child_theme_namespace();

		foreach ( Config::get( self::SNIPPETS_CONFIG ) as $snippet => $args ) {
			// Check if a fully qualified namespace is provided in the config
			if ( strpos( $snippet, '\\' ) !== false ) {
				$class = $snippet;
			} else {
				// Guess the namespace if not provided
				$child_class  = $child_theme_namespace ? "$child_theme_namespace\\Snippets\\$snippet" : null;
				$parent_class = "PressGang\\Snippets\\$snippet";
				$class        = class_exists( $child_class ) ? $child_class : $parent_class;
			}

			if ( $class && in_array( SnippetInterface::class, class_implements( $class ) ) ) {
				( new $class() )->init( $args );
			}
		}
	}

	/**
	 * Retrieves the primary PSR-4 namespace of the child theme.
	 *
	 * This method reads the child theme's composer.json file to extract the PSR-4
	 * namespace. It is assumed that the child theme follows the PSR-4 autoloading standard
	 * for its PHP classes. The method looks for the 'autoload.psr-4' key in the composer.json
	 * file and returns the first namespace found, which is typically the primary namespace
	 * used by the child theme.
	 *
	 * @return string|null The primary PSR-4 namespace of the child theme if found, or null if not.
	 */
	protected function get_child_theme_namespace() {
		$composerJsonPath = \get_stylesheet_directory() . '/composer.json';
		if ( file_exists( $composerJsonPath ) ) {
			$composerConfig = json_decode( file_get_contents( $composerJsonPath ), true );
			if ( isset( $composerConfig['autoload']['psr-4'] ) && is_array( $composerConfig['autoload']['psr-4'] ) ) {
				// Assuming the first key is the namespace you need
				$namespaces = array_keys( $composerConfig['autoload']['psr-4'] );

				return reset( $namespaces ); // Returns the first namespace
			}
		}

		return null;
	}
}
