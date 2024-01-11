<?php

class PasswordProtection {

	/**
	 * Use specific template for password protected posts.
	 *
	 * By default, this will use the **password-protected.php** template file. If you want password
	 * templates specific to a post type, use **password-protected-$posttype.php**.
	 *
	 * The form can be rendered with {{ fn('get_the_password_form') }}
	 *
	 * @see https://timber.github.io/docs/v2/guides/posts/#password-protected-posts
	 */
	public function __construct() {
		add_filter( 'template_include', [ $this, 'get_password_protected_template' ], 99 );
	}

	/**
	 * @param $template
	 *
	 * @return mixed
	 */
	public function get_password_protected_template( $template ) {
		global $post;

		if ( ! empty( $post ) && post_password_required( $post->ID ) ) {
			$template = locate_template( [
				'password-protected.php',
				"password-protected-{$post->post_type}.php",
			] ) ?: $template;
		}

		return $template;
	}

}

new PasswordProtection();
