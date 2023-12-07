<?php

namespace PressGang;

use Timber\TimberLoader;

class ClearTimberCache {

	/**
	 * __construct
	 *
	 * ClearTimberCache constructor.
	 */
	public function __construct() {
		if ( is_user_logged_in() && isset( $_GET['clear_timber_cache'] ) ) {
			$this->clear();
		}
	}

	/**
	 * clear
	 *
	 * Clear Timber and Twig caches
	 */
	public function clear() {
		$loader = new TimberLoader();
		$loader->clear_cache_timber();
		$loader->clear_cache_twig();
	}
}

new ClearTimberCache();
