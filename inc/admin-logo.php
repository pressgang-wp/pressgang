<?php

class AdminLogo {

	/**
	 * init
	 *
	 */
	public function __construct() {
		\add_action( 'login_enqueue_scripts', [ $this, 'add_login_logo' ] );
	}

	/**
	 * add_login_logo
	 *
	 * Replace the WordPress Logo with the Customizer Logo on the wp-admin login screen
	 *
	 * @return void
	 */
	public function add_login_logo(): void {
		if ( $logo = \esc_url( \get_theme_mod( 'logo' ) ) ) : ?>
			<style>
				.login h1 a {
					background-image: url(<?php echo $logo; ?>) !important;
					width: 100% !important;
					max-width: 300px !important;
					-webkit-background-size: contain !important;
					background-size: contain !important;
				}
			</style>
		<?php endif;
	}
}

new AdminLogo();
