<?php
class CookieYes {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'customize_register', array( $this, 'add_to_customizer' ) );
		add_action( 'wp_enqueue_scripts', [ $this, 'cookieyes_header_script' ] );
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function add_to_customizer( $wp_customize ): void {

		if ( ! $wp_customize->get_section( 'cookieyes' ) ) {
			$wp_customize->add_section( 'cookieyes', array(
				'title' => \_x( "CookieYes", 'Customizer', THEMENAME ),
			) );
		}

		$wp_customize->add_setting(
			'cookieyes-id',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'cookieyes-id', array(
			'label'       => \_x( "CookieYes ID", 'Customizer', THEMENAME ),
			'description' => sprintf( \_x( "See %s",  'Customizer', THEMENAME),'https://www.cookieyes.com/' ),
			'section'     => 'cookieyes',
		) ) );
	}

	/**
	 * cookieyes_header_script
	 *
	 * @return void
	 */
	public function cookieyes_header_script() {

		if ( $cookieyes_id = get_theme_mod( 'cookieyes-id' ) ) {
			\wp_enqueue_script( 'cookie-yes', "https://cdn-cookieyes.com/client_data/{$cookieyes_id}/script.js", [] );
		}
	}

}

new CookieYes();
