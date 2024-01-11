<?php

namespace PressGang\Library;

use function PressGang\add_action;

class Tawkto {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_footer', array( $this, 'render' ), 100 );
	}

	/**
	 * render
	 *
	 */
	public function render() {
		\Timber\Timber::render( 'tawkto.twig' );
	}
}

new Tawkto();
