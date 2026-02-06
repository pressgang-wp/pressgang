<?php

namespace PressGang\Configuration;

/**
 * Outputs HTML meta tags in wp_head from config/meta-tags.php. Each entry is a
 * name => content pair rendered as a <meta> element in the document head.
 *
 * Why: keeps global meta tag declarations centralised and out of template markup.
 * Extend via: child theme config override.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta
 */
class MetaTags extends ConfigurationSingleton {

	/**
	 * Initializes the Meta class with configuration data.
	 *
	 * Sets up the meta tags configuration and adds an action hook to output them in the head section.
	 *
	 * @param array $config The configuration array for meta tags.
	 */
	#[\Override]
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

