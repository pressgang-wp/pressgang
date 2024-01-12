<?php

/**
 * Class Copyright
 *
 * Handles the addition of copyright-related settings to the WordPress Customizer.
 */
class Copyright {

	/**
	 * Constructor.
	 *
	 * Adds the setup method to the 'customize_register' action.
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
	}

	/**
	 *
	 * Adds a new section for Footer settings in the Customizer and defines controls for them.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WP_Customize_Manager instance.
	 */
	protected function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {

		if ( ! $wp_customize->get_section( 'footer' ) ) {
			// Add a new section for Footer settings
			$wp_customize->add_section( 'footer', [
				'title'    => \_x( "Footer", "Customizer", THEMENAME ),
				'priority' => 100,
			] );
		}

		// Add setting for Copyright text
		$wp_customize->add_setting(
			'copyright',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		// Add control for Copyright text
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'copyright', [
			'label'   => \_x( "Copyright", 'Customizer', THEMENAME ),
			'section' => 'footer',
		] ) );
	}
}
