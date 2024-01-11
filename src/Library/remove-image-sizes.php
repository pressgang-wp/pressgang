<?php

namespace PressGang\Library;

use function PressGang\add_action;
use function PressGang\get_intermediate_image_sizes;
use function PressGang\remove_image_size;

class RemoveImageSizes {
	/**
	 * __construct
	 *
	 * We mostly don't need these as building responsive images in Timber
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'remove_sizes' ), 1000 );
	}

	/**
	 * remove_sizes
	 */
	public function remove_sizes() {
		foreach ( get_intermediate_image_sizes() as &$size ) {
			remove_image_size( $size );
		}
	}

}

new RemoveImageSizes();
