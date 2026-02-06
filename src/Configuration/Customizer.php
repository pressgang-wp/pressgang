<?php

namespace PressGang\Configuration;

/**
 * Registers WordPress Customizer sections, settings, and controls from
 * config/customizer.php. Supports grouped sections with multiple settings, each
 * with its own control type, sanitisation callback, and transport method.
 *
 * Why: keeps Customizer registration declarative, consistent, and overridable.
 * Extend via: child theme config override or customize_register filter.
 *
 * @see https://codex.wordpress.org/Theme_Customization_API
 */
class Customizer extends ConfigurationSingleton {

	/**
	 * @param array<string, mixed> $config Sections containing settings and control definitions.
	 */
	#[\Override]
	public function initialize( array $config ): void {
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
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
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
	private function add_customizer_sections( \WP_Customize_Manager $wp_customize ): void {
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
	private function add_customizer_settings( \WP_Customize_Manager $wp_customize ): void {
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
	private function create_settings_for_section( \WP_Customize_Manager $wp_customize, string $section, array $settings ): void {
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
	private function create_setting( \WP_Customize_Manager $wp_customize, string $section, string $setting, array $options ): void {
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
	private function add_control_to_setting( $wp_customize, $section, $setting, $options ): void {
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
