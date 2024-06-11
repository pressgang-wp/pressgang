<?php

namespace PressGang\Helpers;

/**
 * Class ScriptLoader
 *
 * Handles the registration of scripts and manages script attributes like async and defer.
 *
 * @package PressGang
 */
class ScriptLoader {

	/**
	 * @var array
	 */
	private static array $async = [];

	/**
	 * @var array
	 */
	private static array $defer = [];

	/**
	 * Registers a script with WordPress and tracks its attributes.
	 *
	 * This method handles the registration of a script based on provided arguments.
	 * It sets default values for the script parameters, processes the script source,
	 * and registers the script to be enqueued later. It also tracks scripts that need
	 * 'defer' or 'async' attributes.
	 *
	 * @param string $handle The script handle.
	 * @param array|string $args An array of arguments for the script, or a string representing the script source URL.
	 *                           Possible arguments include:
	 *                           - 'src' (string)       : The source URL of the script.
	 *                           - 'deps' (array)       : An array of dependencies.
	 *                           - 'ver' (string|null)  : The script version.
	 *                           - 'in_footer' (bool)   : Whether to load the script in the footer.
	 *                           - 'hook' (string)      : The hook to use for enqueuing the script.
	 *                           - 'defer' (bool)       : Whether to add the 'defer' attribute to the script tag.
	 *                           - 'async' (bool)       : Whether to add the 'async' attribute to the script tag.
	 *
	 * @return array The processed script arguments.
	 */
	public static function register_script( string $handle, array|string $args ): array {
		$defaults = [
			'handle'    => $handle,
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
			if ( ! filter_var( $args['src'], FILTER_VALIDATE_URL ) ) {
				// Relative path, script is local to the child theme
				$srcPath     = \get_stylesheet_directory() . '/' . ltrim( $args['src'], '/' );
				$args['ver'] = $args['ver'] ?: ( file_exists( $srcPath ) ? filemtime( $srcPath ) : null );
			}

			// Register scripts
			\add_action( 'wp_loaded', function () use ( $args ) {
				\wp_register_script( $args['handle'], $args['src'], $args['deps'], $args['ver'], $args['in_footer'] );
			} );

			\add_action( $args['hook'], function () use ( $args ) {
				\wp_enqueue_script( $args['handle'] );
			}, 20 );
		}

		// Track scripts that need 'defer' or 'async' attributes
		if ( $args['defer'] ) {
			self::$defer[] = $handle;
		}

		if ( $args['async'] ) {
			self::$async[] = $handle;
		}

		return $args;
	}

	/**
	 * Adds 'defer' or 'async' attributes to script tags.
	 *
	 * @param string $tag The HTML tag for the script.
	 * @param string $handle The script's handle.
	 * @param string $src The script's source URL.
	 *
	 * @return string The modified HTML tag.
	 */
	public static function add_script_attrs( string $tag, string $handle, string $src ): string {
		if ( in_array( $handle, self::$defer ) ) {
			$tag = str_replace( ' src', ' defer="defer" src', $tag );
		}

		if ( in_array( $handle, self::$async ) ) {
			$tag = str_replace( ' src', ' async="async" src', $tag );
		}

		return $tag;
	}
}
