<?php

class RemoveOembedAuthor {

	/**
	 * @return void
	 */
	public function __construct() {
		add_filter( 'oembed_response_data', [ $this, 'disable_embeds_filter_oembed_response_data' ] );
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	public function disable_embeds_filter_oembed_response_data( $data ) {
		unset( $data['author_url'] );
		unset( $data['author_name'] );

		return $data;
	}

}

new RemoveOembedAuthor();
