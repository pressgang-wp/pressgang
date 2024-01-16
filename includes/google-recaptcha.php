<?php

class GoogleRecaptcha {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'customize_register', [ $this, 'customizer' ] );
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function customizer( $wp_customize ) {

		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => __( "Google", THEMENAME ),
			] );
		}

		// recaptcha site key

		$wp_customize->add_setting(
			'google-recaptcha-site-key',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'google-recaptcha-site-key', [
			'label'   => __( "Google Recaptcha Site Key", THEMENAME ),
			'section' => 'google',
			'type'    => 'text',
		] ) );

		// recaptcha secret key

		$wp_customize->add_setting(
			'google-recaptcha-secret',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'google-recaptcha-secret', [
			'label'   => __( "Google Recaptcha Secret", THEMENAME ),
			'section' => 'google',
			'type'    => 'text',
		] ) );
	}

	/**
	 * script
	 *
	 */
	public static function script() {
		\Timber\Timber::render( 'snippets/google-recaptcha.twig' );
	}

}

new GoogleRecaptcha();
