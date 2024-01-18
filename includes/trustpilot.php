<?php

/**
 * Class Trustpilot
 *
 * Handles the integration of Trustpilot widget and customizer settings in a WordPress theme.
 */
class Trustpilot {

	/**
	 * Trustpilot constructor.
	 *
	 * Sets up WordPress customizer options and includes Trustpilot script.
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );
	}

	/**
	 * Add Trustpilot settings to the WordPress Customizer.
	 *
	 * This function registers settings and controls for managing Trustpilot integration
	 * such as Business ID, Template ID, and Reviews URL.
	 *
	 * @param WP_Customize_Manager $wp_customize WordPress Customizer object.
	 */
	public function customizer( $wp_customize ) {

		if ( ! isset( $wp_customize->sections['trustpilot'] ) ) {
			$wp_customize->add_section( 'trustpilot', [
				'title' => __( "Trustpilot", THEMENAME ),
			] );
		}

		// trustpilot business id

		$wp_customize->add_setting( 'trustpilot_business_id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'trustpilot_business_id', [
			'label'   => __( "Business ID", THEMENAME ),
			'section' => 'trustpilot',
			'type'    => 'text',
		] ) );

		// trustpilot template id

		$wp_customize->add_setting( 'trustpilot_template_id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'trustpilot_template_id', [
			'label'   => __( "Template ID", THEMENAME ),
			'section' => 'trustpilot',
			'type'    => 'text',
		] ) );

		// trustpilot reviews url

		$wp_customize->add_setting( 'trustpilot_reviews_link',
			[
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'trustpilot_reviews_link', [
			'label'   => __( "Reviews URL", THEMENAME ),
			'section' => 'trustpilot',
			'type'    => 'text',
		] ) );
	}

	/**
	 * Outputs the Trustpilot script tag in the website's head section.
	 *
	 * This script is necessary for Trustpilot widgets to function properly.
	 */
	public function script() { ?>
		<script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js" async></script>
		<?php
	}
}

new Trustpilot();
