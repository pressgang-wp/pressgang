<?php

/**
 * Class LogoSvg
 *
 * Handles the addition of a logo SVG setting to the WordPress Customizer.
 *
 * Assumes that SVG is enabled in WordPress via an SVG sanitization plugin.
 * Ideally via `composer require darylldoyle/safe-svg`
 *
 * @package PressGang
 */
class LogoSvg {

	/**
	 * Constructor.
	 *
	 * Adds the add_to_customizer method to the 'customize_register' action hook in WordPress.
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
	}

	/**
	 * Add Logo SVG to Customizer.
	 *
	 * Adds a new control for an SVG logo in the WordPress Customizer under the 'logo' section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WP_Customize_Manager instance.
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {

		if ( ! $wp_customize->get_section( 'logo' ) ) {
			$wp_customize->add_section( 'logo', [
				'title'    => \_x( "Logo", 'Customizer', THEMENAME ),
				'priority' => 30,
			] );
		}

		$wp_customize->add_setting( 'logo_svg', [ 'default' => '' ] );

		$wp_customize->add_control( new \WP_Customize_Image_Control( $wp_customize, 'logo_svg', [
			'label'      => _x( "Logo SVG", 'Customizer', THEMENAME ),
			'section'    => 'logo',
			'extensions' => [ 'svg' ],
		] ) );

	}
}

new LogoSvg();
