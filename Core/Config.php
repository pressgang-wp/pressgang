<?php

namespace PressGang\Core;

/**
 * Class Config
 *
 * @package PressGang
 */
class Config {

	public static $settings = array();

	/**
	 * Get the config array
	 *
	 * @param $key - optionally specify a setting param to return
	 *
	 * @return array|mixed
	 */
	public static function get( $key = null, $default = [] ) {
		if ( ! self::$settings ) {

			$parent = require_once get_template_directory() . '/Core/settings.php';

			$parent = is_array( $parent ) ? $parent : [];

			$child = file_exists( get_stylesheet_directory() . '/Core/settings.php' )
				? require_once get_stylesheet_directory() . '/Core/settings.php'
				: [];

			$child = is_array( $child ) ? $child : [];

			self::$settings = apply_filters( 'pressgang_get_settings', array_merge( $parent, $child ) );
			
		}

		if ( $key ) {
			if ( isset( static::$settings[ $key ] ) ) {
				return static::$settings[ $key ];
			}

			return $default;
		}

		return static::$settings;
	}
}
