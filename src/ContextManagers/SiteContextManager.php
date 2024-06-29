<?php

namespace PressGang\ContextManagers;

use Timber\Site;

/**
 * Class SiteContextManager
 *
 * Manages the general context for the PressGang framework by adding general data
 * to the context array, which can be used across various templates. Implements
 * the ContextManagerInterface to ensure compatibility with the PressGang context management system.
 *
 * @package PressGang\ContextManagers
 */
class SiteContextManager implements ContextManagerInterface {

	/**
	 * Adds general data to the context array.
	 *
	 * This method is used to add commonly required data to the context, such as
	 * the stylesheet URL. It can be extended or modified as per theme requirements.
	 *
	 * @param array $context The context array that is passed to templates.
	 *
	 * @return array Modified context array with additional data.
	 */
	public function add_to_context( array $context ): array {
		$site             = new Site();
		$stylesheet       = $this->get_stylesheet( 'styles.css' );
		$site->stylesheet = \apply_filters( 'pressgang_stylesheet', $stylesheet );

		$context['site'] = $site;

		return $context;
	}

	/**
	 * Constructs the stylesheet URL with versioning.
	 *
	 * This function generates a URL for the stylesheet file, appending a version
	 * query parameter to ensure cache busting when the stylesheet file is updated.
	 *
	 * @param string $stylesheet The stylesheet file name.
	 *
	 * @return string The URL for the stylesheet with versioning.
	 */
	protected function get_stylesheet( string $stylesheet ): string {
		$stylesheet_path = "/css/$stylesheet";
		$full_path       = \get_stylesheet_directory() . $stylesheet_path;

		$stylesheet_version = file_exists( $full_path ) ? filemtime( $full_path ) : null;

		$stylesheet_uri = \get_stylesheet_directory_uri() . $stylesheet_path;

		if ( file_exists( $full_path ) ) {
			$stylesheet_uri .= "?v={$stylesheet_version}";
		}

		return $stylesheet_uri;
	}
}
