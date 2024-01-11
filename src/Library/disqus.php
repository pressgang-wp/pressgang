<?php

namespace PressGang\Library;

use function PressGang\__;
use function PressGang\add_action;
use function PressGang\add_filter;
use function PressGang\get_theme_mod;

class Disqus {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'customize_register', array( $this, 'customizer' ) );
		add_filter( 'comments_template', array( $this, 'render' ) );
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function customizer( $wp_customize ) {

		$wp_customize->add_section( 'disqus', array(
			'title' => __( "Disqus", THEMENAME ),
		) );

		$wp_customize->add_setting(
			'disqus-shortname',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'disqus-shortname', array(
			'label'   => __( "Disqus Shortname", THEMENAME ),
			'section' => 'disqus',
		) ) );
	}

	/**
	 * render
	 *
	 * Render disqus.twig
	 *
	 */
	public function render() {
		\Timber\Timber::render( 'disqus.twig', array( 'disqus_shortname' => get_theme_mod( 'disqus-shortname' ), ) );
	}
}

new Disqus();
