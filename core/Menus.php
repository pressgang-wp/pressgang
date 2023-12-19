<?php

namespace PressGang\Core;

use Timber\Timber;

/**
 * Class Menus
 *
 * @package PressGang
 */
class Menus {

	/**
	 * menus
	 *
	 * @var array
	 */
	public static $menus = array();

	/**
	 * __construct
	 *
	 * Register Menus
	 *
	 */
	public function __construct() {
		self::$menus = Config::get( 'menus' );
		add_action( 'init', array( 'PressGang\Menus', 'register' ) );
		add_filter( 'timber/context', array( 'PressGang\Menus', 'add_to_context' ) );
	}

	/**
	 * register
	 *
	 * Register theme menus, filter on 'menu_{$key}'
	 *
	 */
	public static function register() {
		foreach ( static::$menus as $location => &$description ) {
			// allow filtering of the menu in child themes and plugins if required
			$menu = apply_filters( "menu_{$location}", array(
				$location => $description,
			) );

			// remove any invalid items
			if ( ! $menu ) {
				unset( static::$menus[ $location ] );
			}
		}
		register_nav_menus( static::$menus );
	}

	/**
	 * add_to_context
	 *
	 * Add available menus to the Timber context
	 *
	 * @param $context
	 *
	 * @return array
	 */
	public static function add_to_context( $context ) {
		foreach ( static::$menus as $location => &$description ) {
			if ( has_nav_menu( $location ) ) {
				$context["menu_{$location}"] = apply_filters( "menu_{$location}", Timber::get_menu( $location ) );
			}
		}

		return $context;
	}

}

new Menus();
