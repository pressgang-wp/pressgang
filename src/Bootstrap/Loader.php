<?php

namespace PressGang\Bootstrap;

use PressGang\Configuration\ConfigurationInterface;
use function Symfony\Component\String\u;

/**
 * Orchestrates the config-driven boot process. Maps each key in the merged config
 * to a Configuration class (e.g. 'sidebars' → Sidebars) and initialises it, then
 * includes shortcode and widget files from src/.
 */
class Loader {

	/** @var string[] Folders under src/ to include via config. */
	private array $include_folders = [
		'shortcodes',
		'widgets',
	];

	/**
	 * @param ConfigLoaderInterface $configLoader
	 */
	public function __construct( private readonly ConfigLoaderInterface $configLoader ) {
	}

	/**
	 * Initialize the loading of components and inclusion of files.
	 */
	public function initialize(): void {
		$this->load_components();
		$this->include_files();
	}

	/**
	 * Maps each config key to a Configuration class and initialises it.
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
	 * Converts a config key (e.g. 'custom-post-types') to its Configuration class FQCN.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function config_key_to_configuration_class( string $key ): string {
		$studly_case = u( $key )->camel()->title( true );

		return "PressGang\\Configuration\\$studly_case";
	}

	/**
	 * Includes shortcode and widget files listed in config.
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
	 * Requires a file from the given folder and registers its class (widget or shortcode).
	 *
	 * @param string $folder
	 * @param string $file
	 */
	protected function include_file( string $folder, string $file ): void {
		$directories = \apply_filters( "pressgang_include_directories", [
			\get_stylesheet_directory(),
			\get_template_directory(),
		], $folder, $file );

		// Get the child theme's primary namespace or fall back to "PressGang"
		$namespace = get_child_theme_namespace() ?: 'PressGang';

		foreach ( $directories as $directory ) {
			$folder_name = u( $folder )->camel()->title( true );
			$file_path   = "{$directory}/src/{$folder_name}/{$file}.php";

			if ( file_exists( $file_path ) ) {
				require_once $file_path;

				// Construct the full class name using the namespace
				$class_name = "{$namespace}\\{$folder_name}\\{$file}";

				if ( class_exists( $class_name ) ) {
					// Register widgets
					if ( is_subclass_of( $class_name, \PressGang\Widgets\Widget::class ) ) {
						$class_name::register( $class_name );
					}

					// Register shortcodes
					if ( is_subclass_of( $class_name, \PressGang\Shortcodes\Shortcode::class ) ) {
						new $class_name(); // This assumes the constructor handles registration
					}
				}
			}
		}
	}
}
