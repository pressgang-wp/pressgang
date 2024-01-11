<?php

/**
 * Class SumoMe
 *
 * Adds support for SumoMe (https://sumome.com/) traffic tools.
 *
 * @package PressGang
 */
class SumoMe {

	/**
	 * init
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'customize_register', [ $this, 'customizer' ] );
		add_action( 'wp_head', [ $this, 'add_script' ] );
	}

	/**
	 * Add to customizer
	 *
	 * Get Website ID at: https://sumome.com/register
	 *
	 * @param $wp_customize
	 */
	public function customizer( $wp_customize ) {

		$wp_customize->add_section( 'sumome', [
			'title' => __( "Sumo Me", THEMENAME ),
		] );

		$wp_customize->add_setting(
			'sumome-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'sumome-id', [
			'label'   => __( "Sumo Me ID", THEMENAME ),
			'section' => 'sumome',
		] ) );
	}

	/**
	 * script
	 *
	 * @return void
	 */
	public function add_script() {
		if ( $sumome_id = get_theme_mod( 'sumome-id' ) ) {
			\Timber\Timber::render( 'sumome.twig', [ 'sumome_id' => $sumome_id ] );
		}
	}

}

new SumoMe();
