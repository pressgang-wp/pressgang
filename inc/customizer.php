<?php

/**
 * Class Customizer
 *
 * @package PressGang
 */
class Customizer {

	/**
	 * Init
	 *
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'setup' ] );
	}

	/**
	 * Setup all the Customize objects
	 *
	 * @param $wp_customize
	 */
	public function setup( $wp_customize ) {
		$this->main( $wp_customize );
		$this->footer( $wp_customize );
	}

	/**
	 * Main
	 *
	 * @param $wp_customize
	 */
	protected function main( $wp_customize ) {

		// logo

		$wp_customize->add_section( 'logo', [
			'title'    => __( "Logo", THEMENAME ),
			'priority' => 30,
		] );

		$wp_customize->add_setting(
			'logo',
			[
				'default' => '',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Image_Control( $wp_customize, 'logo', [
			'label'   => __( "Logo", THEMENAME ),
			'section' => 'logo',
		] ) );

	}

	/**
	 * Footer
	 *
	 * @param $wp_customize
	 */
	protected function footer( $wp_customize ) {

		$wp_customize->add_section( 'footer', [
			'title'    => __( "Footer", THEMENAME ),
			'priority' => 100,
		] );

		$wp_customize->add_setting(
			'copyright',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'copyright', [
			'label'   => __( "Copyright", THEMENAME ),
			'section' => 'footer',
		] ) );
	}
}

new Customizer();
