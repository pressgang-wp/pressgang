<?php

namespace PressGang\Configuration;

/**
 * Class Styles
 *
 * Manages the registration, enqueueing, and additional attributes of CSS stylesheets in the WordPress theme.
 *
 * Supports adding 'preconnect' attribute to styles for performance optimization.
 *
 * @package PressGang\Configuration
 */
class Styles extends ConfigurationSingleton {

	/**
	 * Array to store URLs for pre-connect.
	 *
	 * @var array
	 */
	public array $preconnect = [];

	/**
	 * Initializes the Styles class with configuration data.
	 *
	 * Registers styles based on provided configuration and adds necessary hooks to WordPress.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
	 *
	 * @param array $config Configuration array for styles.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'init', [ $this, 'register_styles' ] );
		\add_filter( 'style_loader_tag', [ $this, 'add_style_attrs' ], 10, 4 );
	}

	/**
	 * Registers stylesheets in WordPress.
	 *
	 * Iterates through the configuration array and registers each stylesheet,
	 * also enqueues them if specified.
	 *
	 * @see https://codex.wordpress.org/Function_Reference/wp_register_style
	 */
	public function register_styles(): void {

		foreach ( $this->config as $key => $args ) {

			$defaults = [
				'handle'     => $key,
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
				$this->preconnect[ $key ] = filter_var( $args['preconnect'], FILTER_VALIDATE_URL );
			}
		}
	}

	/**
	 * Adds 'preconnect' attribute to the style tag if specified.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/style_loader_tag/
	 * @hooked style_loader_tag
	 * @param string $tag The HTML of the style tag.
	 * @param string $handle The style's handle.
	 * @param string $href The stylesheet's href attribute.
	 * @param string $media The stylesheet's media attribute.
	 *
	 * @return string The modified HTML of the style tag.
	 */
	public function add_style_attrs( string $tag, string $handle, string $href, string $media ): string {

		if ( isset( $this->preconnect[ $handle ] ) ) {
			$url = $this->preconnect[ $handle ];

			// Ensure that the URL is properly escaped
			$escaped_url = \esc_url( $url );

			// Check if 'preconnect' already exists in the HTML
			if ( strpos( $tag, 'preconnect' ) === false ) {
				// Insert the 'preconnect' attribute into the HTML
				$tag = str_replace( ' href', ' preconnect="' . $escaped_url . '" href', $tag );
			}

		}

		return $tag;
	}
}
