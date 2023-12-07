<?php

namespace PressGang;

class StructuredDataPageMap {
	public function __construct() {
		add_action( 'wp_head', array( $this, 'render' ) );
	}

	public function render() {
		return \Timber\Timber::render( 'structured-data-page-map.twig' );
	}
}

new StructuredDataPageMap();
