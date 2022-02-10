<?php

namespace PressGang;

class RemovePosts {

	/**
	 * RemovePosts constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'post_remove' ) );
	}

	/**
	 * Removes posts from the admin menu
	 */
	public function post_remove() {
		remove_menu_page( 'edit.php' );
	}
}

new RemovePosts();
