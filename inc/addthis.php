<?php

namespace PressGang;

class AddThis {

	protected $consented = false;

	/**
	 * init
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'customize_register', array( $this, 'customizer' ) );
		add_shortcode( 'addthis', array( $this, array( $this, 'button' ) ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );

		$this->consented = isset( $_COOKIE['cookie-consent'] ) && ! ! $_COOKIE['cookie-consent'];
		$this->register_script();
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function customizer( $wp_customize ) {

		$wp_customize->add_section( 'addthis', array(
			'title' => __( "AddThis", THEMENAME ),
		) );

		$wp_customize->add_setting(
			'addthis-id',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'addthis-id', array(
			'label'   => __( "AddThis ID", THEMENAME ),
			'section' => 'addthis',
		) ) );

		$wp_customize->add_setting(
			'addthis-class',
			array(
				'default'           => 'addthis_native_toolbox',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'addthis-class', array(
			'label'   => __( "AddThis Toolbox Class", THEMENAME ),
			'section' => 'addthis',
		) ) );
	}

	/**
	 * script
	 *
	 * Go to www.addthis.com/dashboard to customize your tools
	 *
	 * @return void
	 */
	public function register_script() {
		if ( ! EXPLICIT_CONSENT || $this->consented ) {
			if ( $addthis_id = urlencode( get_theme_mod( 'addthis-id' ) ) ) {

				Scripts::$scripts['addthis'] = array(
					'src'       => "//s7.addthis.com/js/300/addthis_widget.js#pubid={$addthis_id}",
					'deps'      => array(),
					'ver'       => '8.28.7',
					'in_footer' => true,
					'defer'     => true,
					'async'     => true,
					'hook'      => 'show_addthis',
				);
			}
		}
	}

	/**
	 * add_to_twig
	 *
	 * Add a function to the Twig scope
	 *
	 * @param $twig
	 *
	 * @return mixed
	 */
	public function add_to_twig( $twig ) {
		$twig->addFunction( new \Twig_SimpleFunction( 'addthis', array( $this, 'button' ) ) );

		return $twig;
	}

	/**
	 * button
	 *
	 * Displays the addthis sharing button configured on the addthis.com dashboard page
	 *
	 */
	public function button() {

		if ( ! EXPLICIT_CONSENT || $this->consented ) {
			if ( $addthis_id = get_theme_mod( 'addthis-id' ) ) {

				do_action( 'show_addthis' );
				\Timber\Timber::render( 'partials/addthis.twig', array( 'addthis_class' => get_theme_mod( 'addthis-class' ) ) );

			}
		}
	}
}

new AddThis();
