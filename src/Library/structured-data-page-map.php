<?php

namespace PressGang\Library;

use function PressGang\add_action;

class StructuredDataPageMap {
	public function __construct() {
		add_action( 'wp_head', array( $this, 'render' ) );
	}

	public function render() {
		return \Timber\Timber::render( 'structured-data-page-map.twig' );
	}
}

new StructuredDataPageMap();
