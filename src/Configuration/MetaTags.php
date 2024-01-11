<?php

namespace PressGang\Configuration;

/**
 * Class MetaTags
 *
 * Manages the addition of HTML meta tags to the head section of the WordPress site.
 * The class uses a configuration array to define the meta tags that should be added.
 * It extends ConfigurationSingleton to ensure that it is only instantiated once.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta
 * @package PressGang
 */
class MetaTags extends ConfigurationSingleton {

	/**
	 * Initializes the Meta class with configuration data.
	 *
	 * Sets up the meta tags configuration and adds an action hook to output them in the head section.
	 *
	 * @param array $config The configuration array for meta tags.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'wp_head', [ $this, 'add_meta_tags' ] );
	}

	/**
	 * Outputs the meta tags in the head section of the site.
	 *
	 * Iterates through the meta configuration and prints each meta tag.
	 *
	 * @hooked action 'wp_head'
	 */
	public function add_meta_tags(): void {
		foreach ( $this->config as $name => $content ) {
			echo sprintf( '<meta name="%s" content="%s">', \esc_attr( $name ), \esc_attr( $content ) );
		}
	}
}

