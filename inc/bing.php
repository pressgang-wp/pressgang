<?php

class Bing {

	/**
	 * __construct
	 *
	 * Adds a bing customizer field
	 *
	 * @return void
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function customizer( $wp_customize ) {

		if ( ! isset( $wp_customize->sections['microsoft'] ) ) {
			$wp_customize->add_section( 'microsoft', [
				'title' => \__( "Microsoft", THEMENAME ),
			] );
		}

		$wp_customize->add_setting(
			'bing_verification_code',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'bing_verification_code', [
			'label'       => \__( "Bing Verification Code", THEMENAME ),
			'description' => sprintf( \__( "See %s" ), 'goo.gl/xeaAOv' ),
			'section'     => 'microsoft',
		] ) );
	}
}

new Bing();
