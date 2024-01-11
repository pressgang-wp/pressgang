<?php

namespace PressGang\Library;

use PressGang\WP_Post;
use PressGang\WP_Theme;
use function PressGang\add_filter;

class HideSinglePageTemplate {

	/**
	 * constuctor
	 */
	public function __construct() {
		add_filter( 'theme_page_templates', [ $this, 'hide_single_page_template' ], 20, 1 );
	}

	/**
	 * Filter the theme page templates.
	 *
	 * @param array $page_templates Page templates.
	 * @param WP_Theme $this WP_Theme instance.
	 * @param WP_Post $post The post being edited, provided for context, or null.
	 *
	 * @return array Modified page templates array.
	 */
	public function hide_single_page_template( $page_templates ) {

		if ( isset( $page_templates['page-templates/single-page.php'] ) ) {
			unset( $page_templates['page-templates/single-page.php'] );
		}

		return $page_templates;
	}

}

new HideSinglePageTemplate();
