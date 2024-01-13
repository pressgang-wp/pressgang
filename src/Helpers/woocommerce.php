<?php

/**
 * @see https://timber.github.io/docs/v2/guides/woocommerce/#tease-product
 * @param $post
 *
 * @return void
 */
function timber_set_product( $post ): void {
	global $product;

	if ( \is_woocommerce() ) {
		$product = \wc_get_product( $post->ID );
	}
}
