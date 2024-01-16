<?php

if ( ! defined( 'EXPLICIT_CONSENT' ) ) {
	define( "EXPLICIT_CONSENT", false );
}

class GoogleTagManager {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'customize_register', [ $this, 'customizer' ] );
		add_action( 'wp_head', [ $this, 'script' ], 500 );
		add_action( 'wp_body_open', [ $this, 'no_script' ] );
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

		// tag manager id

		$wp_customize->add_setting(
			'google-tag-manager-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'google-tag-manager-id', [
			'label'   => __( "Google Tag Manager ID", THEMENAME ),
			'section' => 'google',
			'type'    => 'text',
		] ) );

	}

	/**
	 * script
	 *
	 * @return void
	 */
	public function script() {
		if ( $google_tag_manager_id = get_theme_mod( 'google-tag-manager-id' ) ) {
			\Timber\Timber::render( 'snippets/google-tag-manager.twig', [
				'google_tag_manager_id' => $google_tag_manager_id,
			] );
		}
	}

	/**
	 * no_script
	 *
	 * @return void
	 */
	public function no_script() {
		if ( $google_tag_manager_id = get_theme_mod( 'google-tag-manager-id' ) ) {
			\Timber\Timber::render( 'snippets/google-tag-manager-no-script.twig', [
				'google_tag_manager_id' => $google_tag_manager_id,
			] );
		}
	}
}

new GoogleTagManager();
