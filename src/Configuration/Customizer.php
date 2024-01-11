<?php

namespace PressGang\Configuration;

/**
 * Class Customizer
 *
 * Manages the WordPress Theme Customization API, facilitating the addition of new settings and controls to the WordPress theme customizer.
 * It enables the theme to offer customizable options to the user through a coherent and integrated interface in the WordPress admin.
 * This class dynamically reads configuration settings and applies them to the customizer, handling both the creation of new sections and
 * the registration of new settings and controls within those sections.
 *
 * @see https://codex.wordpress.org/Theme_Customization_API
 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/customize_register
 * @see https://codex.wordpress.org/Class_Reference/WP_Customize_Manager/add_setting
 * @see https://codex.wordpress.org/Class_Reference/WP_Customize_Manager/add_control
 * @package PressGang\Configuration
 */
class Customizer extends ConfigurationSingleton {

	/**
	 * Initializes the Customizer settings based on the provided configuration.
	 *
	 * This method registers the 'customize_register' action hook to customize WordPress Theme Customization options.
	 * It interprets the provided configuration array to add new sections, settings, and controls to the theme customizer.
	 *
	 * @hooked customize_register
	 *
	 * @param array $config Configuration array defining the customizer settings and controls.
	 */
	public function initialize( $config ) {
		$this->config = $config;
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ], 100 );
	}

	/**
	 * Configures customizer settings and controls.
	 *
	 * Main method to configure the customizer with settings and controls.
	 *
	 * This method sets up customizer sections and settings based on the configuration array provided.
	 *
	 * Config format is as follows:
	 *
	 * 'customizer' => {
	 *    'section' => {
	 *        'title' => "Section Title",
	 *        'settings' =>
	 *          'setting' => {
	 *            // setting fields
	 *            'default' => 'Default', // default field value
	 *            'sanitize_callback' => 'sanitize_text_field' // sanitization callback
	 *            // control fields
	 *            'class' => '\WP_Customize_Image_Control'
	 *            'label' => "",
	 *            'description' => "",
	 *            'type' => 'text'
	 *          }
	 *       }
	 *    }
	 * }
	 *
	 *
	 * @param \WP_Customize_Manager $wp_customize WordPress Customizer Manager object.
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ) {
		$this->add_customizer_sections( $wp_customize );
		$this->add_customizer_settings( $wp_customize );
	}

	/**
	 * Adds customizer sections based on the configuration.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WP Customizer Manager object.
	 *
	 * @return void
	 */
	private function add_customizer_sections( \WP_Customize_Manager $wp_customize ) {
		foreach ( $this->config as $section => $args ) {
			if ( ! $wp_customize->get_section( $section ) ) {
				$title = $args['title'] ?? ucwords( str_replace( [ '-', '_' ], ' ', $section ) );
				$wp_customize->add_section( $section, [ 'title' => $title ] );
			}
		}
	}

	/**
	 * Adds settings for each customizer section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WP Customizer Manager object.
	 */
	private function add_customizer_settings( \WP_Customize_Manager $wp_customize ) {
		foreach ( $this->config as $section => $args ) {
			$this->create_settings_for_section( $wp_customize, $section, $args['settings'] );
		}
	}

	/**
	 * Creates settings for a specific customizer section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WP Customizer Manager object.
	 * @param string $section The section ID.
	 * @param array $settings Array of settings for the section.
	 */
	private function create_settings_for_section( \WP_Customize_Manager $wp_customize, string $section, array $settings ) {
		foreach ( $settings as $setting => $options ) {
			$this->create_setting( $wp_customize, $section, $setting, $options );
		}
	}

	/**
	 * Creates a specific customizer setting.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WP Customizer Manager object.
	 * @param string $section The section ID.
	 * @param string $setting The setting ID.
	 * @param array $options Options for the setting.
	 */
	private function create_setting( \WP_Customize_Manager $wp_customize, string $section, string $setting, array $options ) {
		if ( is_numeric( $setting ) ) {
			$setting = $options;
			$options = [];
		}

		$sanitize_callback = $options['sanitize_callback'] ?? 'sanitize_text_field';
		$default           = $options['default'] ?? null;

		if ( ! $wp_customize->get_setting( $setting ) ) {
			$wp_customize->add_setting( $setting, [
				'default'           => $default,
				'sanitize_callback' => $sanitize_callback
			] );
			$this->add_control_to_setting( $wp_customize, $section, $setting, $options );
		}
	}

	/**
	 * Adds a control to a specific customizer setting.
	 *
	 * @param WP_Customize_Manager $wp_customize The WP Customizer Manager object.
	 * @param string $section The section ID.
	 * @param string $setting The setting ID.
	 * @param array $options Options for the control.
	 */
	private function add_control_to_setting( $wp_customize, $section, $setting, $options ) {
		$class = $options['class'] ?? 'WP_Customize_Control';
		$class = "\\{$class}";

		if ( ! class_exists( $class ) ) {
			// Handle error or log the issue
			return;
		}

		$label       = $options['label'] ?? ucwords( str_replace( [ '-', '_' ], ' ', $setting ) );
		$description = $options['description'] ?? '';
		$priority    = $options['priority'] ?? 10;
		$type        = $options['type'] ?? null;

		$wp_customize->add_control( new $class( $wp_customize, $setting, [
			'label'       => $label,
			'description' => $description,
			'section'     => $section,
			'priority'    => $priority,
			'type'        => $type,
		] ) );
	}

}
