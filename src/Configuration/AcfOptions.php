<?php

namespace PressGang\Configuration;

/**
 * Class AcfOptions
 *
 * Handles the creation of Advanced Custom Fields (ACF) options pages based on a provided configuration array.
 * This class extends ConfigurationSingleton to ensure that it is instantiated only once.
 *
 * @see https://www.advancedcustomfields.com/resources/acf_add_options_page/
 * @package PressGang\Configuration
 */
class AcfOptions extends ConfigurationSingleton {

	/**
	 * Initializes the AcfOptions class with configuration data.
	 *
	 * Sets up the configuration and adds an action hook to create ACF options pages.
	 *
	 * @param array $config The configuration array for ACF options pages.
	 */
	public function initialize( $config ) {
		$this->config = $config;
		add_action( 'init', [ $this, 'add_options_pages' ] );
	}

	/**
	 * Adds ACF options pages based on the provided configuration.
	 *
	 * Iterates through the configuration array and uses ACF's acf_add_options_page() function
	 * to create options pages. Each array entry should define the settings for one ACF options page.
	 *
	 * @see https://www.advancedcustomfields.com/resources/acf_add_options_page/
	 * @return void
	 */
	public function add_options_pages() {

		foreach ( $this->config as $key => $options ) {
			if ( function_exists( 'acf_add_options_page' ) ) {

				if(!isset($options['menu_slug'])) {
					$options['menu_slug'] = $key;
				}

				\acf_add_options_page( $options );
			}
		}
	}
}
