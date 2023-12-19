<?php

namespace PressGang;

class HideH1Headings {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tiny_mce_before_init', array( $this, 'remove_h1_headings' ) );
	}

	/**
	 * remove_h1_headings
	 *
	 */
	public function remove_h1_headings( $in ) {
		$in['block_formats'] = "Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6;";

		return $in;
	}

}

new HideH1Headings();
