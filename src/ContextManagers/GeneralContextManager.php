<?php

namespace PressGang\ContextManagers;

/**
 * Class GeneralContextManager
 *
 * Manages the general context for the PressGang framework by adding general data
 * to the context array, which can be used across various templates. Implements
 * the ContextManagerInterface to ensure compatibility with the PressGang context management system.
 *
 * @package PressGang\ContextManagers
 */
class GeneralContextManager implements ContextManagerInterface {

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
	public function add_to_context( $context ) {

		$stylesheet = $this->get_stylesheet( 'styles.css' );
		$stylesheet = \apply_filters( 'pressgang_stylesheet', $stylesheet );

		$context['stylesheet'] = $stylesheet;

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
	protected function get_stylesheet( $stylesheet ) {
		$stylesheetPath    = "/css/$stylesheet";
		$stylesheetVersion = filemtime( \get_stylesheet_directory() . $stylesheetPath );

		return \get_stylesheet_directory_uri() . $stylesheetPath . "?v=" . $stylesheetVersion;
	}
}
