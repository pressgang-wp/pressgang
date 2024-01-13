<?php

/**
 * Class AdminLogo
 *
 * Customizes the WordPress admin login logo with the theme's logo.
 */
class AdminLogo {

	/**
	 * Constructor.
	 *
	 * Hooks into WordPress to change the admin login logo.
	 */
	public function __construct() {
		\add_action( 'login_enqueue_scripts', [ $this, 'add_login_logo' ] );
	}

	/**
	 * Adds custom CSS to the WordPress login page to replace the default WordPress logo.
	 *
	 * Only adds the logo if a custom logo has been set in the theme customizer.
	 */
	public function add_login_logo(): void {

		$logo = \get_theme_mod( 'logo' ) ?? \get_theme_mod( 'logo-svg' );

		if ( $logo ) {
			echo $this->generate_login_logo_css( $logo );
		}
	}

	/**
	 * Generates CSS for customizing the login logo.
	 *
	 * @param string $logo_url URL of the custom logo.
	 *
	 * @return string CSS code for the custom login logo.
	 */
	protected function generate_login_logo_css( string $logo_url ): string {

		$logo_url = \esc_url( $logo_url );

		return "
            <style>
                .login h1 a {
                    background-image: url({$logo_url}) !important;
                    width: 100% !important;
                    max-width: 300px !important;
                    -webkit-background-size: contain !important;
                    background-size: contain !important;
                }
            </style>
        ";
	}
}

new AdminLogo();
