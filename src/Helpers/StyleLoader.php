<?php

namespace PressGang\Helpers;

/**
 * Registers and enqueues CSS stylesheets with WordPress, supporting cache-busted
 * versioning for local files and optional preconnect link injection for external
 * resources. Called by the Styles configuration class for each entry in config/styles.php.
 */
class StyleLoader {

	/**
	 * Array to store URLs for pre-connect.
	 *
	 * @var array
	 */
	public static array $preconnect = [];

	/**
	 * @param $handle
	 * @param $args
	 *
	 * @return array
	 */
	public static function register_style( string $handle, array|string $args ): array {

		$defaults = [
			'handle'     => $handle,
			'src'        => '',
			'deps'       => [],
			'ver'        => null,
			'media'      => 'all',
			'hook'       => 'wp_enqueue_scripts',
			'preconnect' => null,
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

			// Register styles
			\add_action( 'wp_loaded', function () use ( $args, $ver ) {
				\wp_register_style( $args['handle'], $args['src'], $args['deps'], $ver, $args['media'] );
			} );

			// Enqueue styles on the given hook
			\add_action( $args['hook'], function () use ( $args ) {
				\wp_enqueue_style( $args['handle'] );
			} );
		}

		if ( $args['preconnect'] ) {
			static::$preconnect[ $handle ] = filter_var( $args['preconnect'], FILTER_VALIDATE_URL );
		}

		return $args;
	}


	/**
	 * Adds 'preconnect' attribute to the style tag if specified.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/style_loader_tag/
	 * @hooked style_loader_tag
	 *
	 * @param string $tag The HTML of the style tag.
	 * @param string $handle The style's handle.
	 * @param string $href The stylesheet's href attribute.
	 * @param string $media The stylesheet's media attribute.
	 *
	 * @return string The modified HTML of the style tag.
	 */
	public static function add_style_attrs( string $tag, string $handle, string $href, string $media ): string {

		if ( isset( static::$preconnect[ $handle ] ) ) {
			$url = static::$preconnect[ $handle ];

			// Ensure that the URL is properly escaped
			$escaped_url = \esc_url( $url );

			// Check if 'preconnect' already exists in the HTML
			if ( ! str_contains( $tag, 'preconnect' ) ) {
				// Insert the 'preconnect' attribute into the HTML
				$tag = str_replace( ' href', ' preconnect="' . $escaped_url . '" href', $tag );
			}

		}

		return $tag;
	}
}
