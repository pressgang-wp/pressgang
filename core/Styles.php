<?php

namespace PressGang\Core;

class Styles {

	/**
	 * Styles
	 *
	 * @var array|mixed
	 */
	public static $styles = array();

	/**
	 * Default styles to dequeue
	 *
	 * @var array
	 */
	public static $dequeue_styles = array();

	/**
	 * Preconnect styles
	 *
	 * @var array
	 */
	public static $preconnect = array();

	/**
	 * __construct
	 *
	 * Adds scripts from the settings file to be enqueued on the given hooks (default = 'wp_enqueue_scripts')
	 *
	 * See - https://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
	 *
	 */
	public function __construct() {

		static::$styles         = Config::get( 'styles' );
		static::$dequeue_styles = Config::get( 'dequeue_styles' );

		add_action( 'init', array( 'PressGang\Core\Styles', 'register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( 'PressGang\Core\Styles', 'dequeue_styles' ) );
		add_filter( 'style_loader_tag', array( 'PressGang\Core\Styles', 'add_style_attrs' ), 10, 4 );
	}

	/**
	 * register_styles
	 *
	 * See - https://codex.wordpress.org/Function_Reference/wp_register_style
	 *
	 */
	public static function register_styles() {

		foreach ( static::$styles as $key => &$args ) {

			$defaults = array(
				'handle'     => $key,
				'src'        => '',
				'deps'       => array(),
				'ver'        => null,
				'media'      => 'all',
				'hook'       => 'wp_enqueue_scripts',
				'preconnect' => null,
			);

			if ( is_string( $args ) ) {
				// TODO could validate URL?
				// TODO could - get_template_directory_uri
				$args['src'] = $args;
			}

			if ( is_array( $args ) ) {
				$args = wp_parse_args( $args, $defaults );
			}

			if ( isset( $args['src'] ) && $args['src'] ) {

				// register scripts
				add_action( 'wp_loaded', function () use ( $args ) {
					// TODO filemtime()
					$ver = isset( $args['version'] ) ? $args['version'] : ( isset( $args['ver'] ) ? $args['ver'] : null );
					wp_register_style( $args['handle'], $args['src'], $args['deps'], $ver, $args['media'] );
				} );

				// enqueue on given hook
				add_action( $args['hook'], function () use ( $args ) {
					wp_enqueue_style( $args['handle'] );
				} );
			}

			if ( $args['preconnect'] ) {
				static::$preconnect[ $key ] = filter_var( $args['preconnect'], FILTER_VALIDATE_URL );
			}
		}
	}

	/**
	 * dequeue_styles
	 *
	 * @return void
	 */
	public static function dequeue_styles() {
		foreach ( static::$dequeue_styles as $style ) {
			wp_dequeue_style( $style );
		}
	}

	/**
	 * add_style_attrs
	 *
	 * @param $tag
	 * @param $handle
	 *
	 * @return mixed
	 */
	public static function add_style_attrs( $html, $handle, $href, $media ) {
		if ( isset( Styles::$preconnect[ $handle ] ) ) {
			$url = Styles::$preconnect[ $handle ];

			$html = str_replace( ' href', sprintf( ' preconnect="%s" href', urlencode( $url ) ), $html );

			$html = '<link rel="preconnect" href="https://fonts.googleapis.com">' . PHP_EOL . $html;
		}

		return $html;
	}
}

new Styles();
