<?php

namespace PressGang\Configuration;

/**
 * Class Scripts
 *
 * Manages the registration and de-registration of JavaScript scripts in WordPress.
 * It allows for scripts to be added and modified with attributes like async and defer.
 *
 * @package PressGang
 */
class Scripts extends ConfigurationSingleton {

	/**
	 * scripts
	 *
	 * @var array
	 */
	public $scripts = [];

	/**
	 * @var array
	 */
	public $async = [];

	/**
	 * @var array
	 */
	public $defer = [];

	/**
	 * Initializes the Scripts class with configuration data.
	 *
	 * Registers scripts based on provided configuration to be enqueued on the given hooks ( default = 'wp_enqueue_scripts' )
	 *
	 * @param array $config The configuration array for scripts.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'register_scripts' ] );
		\add_filter( 'script_loader_tag', [ $this, 'add_script_attrs' ], 10, 3 );
	}

	/**
	 * Registers and enqueues scripts based on provided configurations.
	 *
	 * Iterates over each script configuration, sets default parameters, registers, and enqueues scripts.
	 *
	 * Handles additional attributes like 'async' and 'defer' for script loading.
	 *
	 * @see https://codex.wordpress.org/Function_Reference/wp_register_script
	 */
	public function register_scripts(): void {
		foreach ( $this->config as $key => $args ) {

			$defaults = [
				'handle'    => $key,
				'src'       => '',
				'deps'      => [],
				'ver'       => null,
				'in_footer' => false,
				'hook'      => 'wp_enqueue_scripts',
				'defer'     => false,
				'async'     => false,
			];

			// If $args is a string, treat it as the 'src' of the script
			if ( is_string( $args ) ) {
				$args = [ 'src' => $args ];
			}

			// Merge provided arguments with defaults
			$args = \wp_parse_args( $args, $defaults );

			if ( isset( $args['src'] ) && $args['src'] ) {
				// Check if the script source is an absolute URL
				if ( filter_var( $args['src'], FILTER_VALIDATE_URL ) ) {
					// Absolute URL, likely an external script
					$ver = $args['ver'];
				} else {
					// Relative path, script is local to the child theme
					$srcPath = \get_stylesheet_directory() . '/' . ltrim( $args['src'], '/' );
					$ver     = $args['ver'] ?: ( file_exists( $srcPath ) ? filemtime( $srcPath ) : null );
				}

				// Register and enqueue scripts
				\add_action( 'wp_loaded', function () use ( $args, $ver ) {
					\wp_register_script( $args['handle'], $args['src'], $args['deps'], $ver, $args['in_footer'] );
				} );

				\add_action( $args['hook'], function () use ( $args ) {
					\wp_enqueue_script( $args['handle'] );
				}, 20 );
			}

			// Track scripts that need 'defer' or 'async' attributes
			if ( $args['defer'] ) {
				$this->defer[] = $key;
			}

			if ( $args['async'] ) {
				$this->async[] = $key;
			}

		}
	}

	/**
	 * Adds 'defer' or 'async' attributes to script tags.
	 *
	 * @param string $tag The HTML tag for the script.
	 * @param string $handle The script's handle.
	 *
	 * @return string The modified HTML tag.
	 */
	public function add_script_attrs( string $tag, string $handle ): string {
		if ( in_array( $handle, $this->defer ) ) {
			$tag = str_replace( ' src', ' defer="defer" src', $tag );
		}

		if ( in_array( $handle, $this->async ) ) {
			$tag = str_replace( ' src', ' async="async" src', $tag );
		}

		return $tag;
	}
}
