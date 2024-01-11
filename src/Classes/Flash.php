<?php

namespace PressGang\Classes;

class Flash {

	/**
	 * session
	 */
	protected static function session() {
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * get
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {

		self::session();

		return $_SESSION['flash'][ $key ] ?? $default;
	}

	/**
	 * add
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function add( $key, $value ) {

		self::session();

		return $_SESSION['flash'][ $key ] = $value;
	}

	/**
	 * delete
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function delete( $key ) {

		self::session();

		unset( $_SESSION['flash'], $key );
	}

	/**
	 * clear
	 *
	 * clear session flash items
	 */
	public static function clear() {
		self::session();
		if ( isset( $_SESSION['flash'] ) ) {
			unset( $_SESSION['flash'] );
		}
	}
}
