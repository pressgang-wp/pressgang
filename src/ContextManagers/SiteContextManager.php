<?php

namespace PressGang\ContextManagers;

use Timber\Site;

/**
 * Adds the Timber Site object and a cache-busted stylesheet URL to the global
 * context. Available in all templates as {{ site }} and {{ site.stylesheet }}.
 */
class SiteContextManager implements ContextManagerInterface {

	/**
	 * @param array<string, mixed> $context
	 *
	 * @return array<string, mixed>
	 */
	public function add_to_context( array $context ): array {
		$site             = new Site();
		$stylesheet       = $this->get_stylesheet( 'styles.css' );
		$site->stylesheet = \apply_filters( 'pressgang_stylesheet', $stylesheet );

		$context['site'] = $site;

		return $context;
	}

	/**
	 * Builds a cache-busted stylesheet URL using the file's modified time.
	 *
	 * @param string $stylesheet
	 *
	 * @return string
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
