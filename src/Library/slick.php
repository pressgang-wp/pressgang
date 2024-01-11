<?php

namespace PressGang\Library;

use PressGang\Scripts;
use function PressGang\get_template_directory_uri;

class Slick {
	public function __construct() {
		// enqueue slick.js
		Scripts::$scripts['slick'] = array(
			'src'       => get_template_directory_uri() . '/js/src/vendor/slick/slick.js',
			'deps'      => array( 'jquery' ),
			'ver'       => '1.9.1',
			'in_footer' => true
		);

		// enqueue pressgang's slick init
		Scripts::$scripts['pressgang-slick'] = array(
			'src'       => get_template_directory_uri() . '/js/src/custom/slick.js',
			'deps'      => array( 'slick' ),
			'ver'       => '0.1',
			'in_footer' => true
		);
	}
}

new Slick();
