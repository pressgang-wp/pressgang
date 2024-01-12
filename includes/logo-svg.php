<?php

/**
 * Class LogoSvg
 *
 * TODO assume `composer require darylldoyle/safe-svg`
 *
 * @package PressGang
 */
class LogoSvg {

	public function __construct() {
		\add_action( 'customize_register', [ $this, 'logo_svg' ] );
	}

	/**
	 * logo_svg
	 *
	 * @param $wp_customize
	 */
	public static function logo_svg( $wp_customize ) {

		if ( ! $wp_customize->get_section( 'logo' ) ) {
			$wp_customize->add_section( 'logo', [
				'title'    => __( "Logo", THEMENAME ),
				'priority' => 30,
			] );
		}

		$wp_customize->add_setting(
			'logo_svg',
			[
				'default' => '',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Image_Control( $wp_customize, 'logo_svg', [
			'label'      => __( "Logo SVG", THEMENAME ),
			'section'    => 'logo',
			'extensions' => [ 'svg' ],
		] ) );

	}
}

new LogoSvg();
