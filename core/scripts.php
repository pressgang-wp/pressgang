<?php

namespace PressGang;

class Scripts {

	/**
	 * scripts
	 *
	 * @var array
	 */
	public static $scripts = array();

	/**
	 * deregister_scripts
	 *
	 * @var array
	 */
	public static $deregister_scripts = array();

	/**
	 * @var array
	 */
	public static $async = array();

	/**
	 * @var array
	 */
	public static $defer = array();

	/**
	 * __construct
	 *
	 * Adds scripts from the settings file to be enqueued on the given hooks (default = 'wp_enqueue_scripts')
	 *
	 * See - https://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
	 *
	 */
	public function __construct() {
		static::$scripts = Config::get( 'scripts' );
		add_action( 'init', array( $this, 'register_scripts' ) );
		add_action( 'init', array( $this, 'deregister_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_script_attrs' ), 10, 3 );
	}

	/**
	 * register_scripts
	 *
	 * See - https://codex.wordpress.org/Function_Reference/wp_register_script
	 *
	 */
	public function register_scripts() {
		foreach ( static::$scripts as $key => &$args ) {

			$defaults = array(
				'handle'    => $key,
				'src'       => '',
				'deps'      => array(),
				'ver'       => false,
				'in_footer' => false,
				'hook'      => 'wp_enqueue_scripts',
				'defer'     => false,
				'async'     => false,
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

				// TODO filemtime()
				$ver = isset( $args['version'] ) ? $args['version'] : ( isset( $args['ver'] ) ? $args['ver'] : '1.0.0' );

				// register scripts
				add_action( 'wp_loaded', function () use ( $args, $ver ) {
					wp_register_script( $args['handle'], $args['src'], $args['deps'], $ver, $args['in_footer'] );
				} );

				// enqueue on given hook
				add_action( $args['hook'], function () use ( $args, $ver ) {
					wp_enqueue_script( $args['handle'], $args['src'], $args['deps'], $ver, $args['in_footer'] );
				}, 20 );
			}

			if ( $args['defer'] ) {
				static::$defer[] = $key;
			}

			if ( $args['async'] ) {
				static::async[] = $key;
			}

		}
	}

	/**
	 * add_script_attrs
	 *
	 * @param $tag
	 * @param $handle
	 *
	 * @return mixed
	 */
	public function add_script_attrs( $tag, $handle ) {
		if ( in_array( $handle, static::$defer ) ) {
			$tag = str_replace( ' src', ' defer="defer" src', $tag );
		}

		if ( in_array( $handle, static::$async ) ) {
			$tag = str_replace( ' src', ' async="async" src', $tag );
		}

		return $tag;
	}

	/**
	 * deregister_scripts
	 *
	 * Can be used for unloading jQuery etc.
	 *
	 */
	public function deregister_scripts() {
		if ( ! is_admin() ) {

			foreach ( static::$deregister_scripts as $key => &$args ) {

				add_action( 'wp_enqueue_scripts', function () use ($key) {
					wp_deregister_script( $key );
				}, 0 );

			}

		}
	}

}

new Scripts();
