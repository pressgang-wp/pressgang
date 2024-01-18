<?php

/**
 * Class Logo
 *
 * Handles the addition of a logo setting to the WordPress Customizer.
 *
 * @package PressGang
 */
class Logo {

	/**
	 * Constructor.
	 *
	 * Adds the add_logo method to the 'customize_register' action hook in WordPress.
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
	}

	/**
	 * Add Logo.
	 *
	 * Adds a new section for the logo in the WordPress Customizer and defines controls for it.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WP_Customize_Manager instance.
	 */
	public function add_to_customizer( WP_Customize_Manager $wp_customize ): void {

		if ( ! $wp_customize->get_section( 'logo' ) ) {
			// Add a new section for Logo settings
			$wp_customize->add_section( 'logo', [
				'title'    => \_x( "Logo", "Customizer", THEMENAME ),
				'priority' => 30,
			] );
		}

		$wp_customize->add_setting( 'logo', [ 'default' => '' ] );

		$wp_customize->add_control( new \WP_Customize_Image_Control( $wp_customize, 'logo', [
			'label'   => __( "Logo", THEMENAME ),
			'section' => 'logo',
		] ) );

	}

}

new Logo();
