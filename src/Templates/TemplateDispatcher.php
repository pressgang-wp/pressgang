<?php

namespace PressGang\Templates;

use PressGang\Controllers\ControllerFactory;

/**
 * Routes requests that fell through to a parent theme template onto a
 * child-theme controller, removing the need for per-template PHP stubs.
 *
 * When WordPress resolves a request to a template inside the parent theme
 * (i.e. the child theme has no stub for it) and one of the recorded
 * hierarchy candidates maps to a controller — via config/controllers.php or
 * the child theme's \Controllers namespace convention — the request is
 * redirected to the parent's dispatch.php, which renders that controller.
 *
 * A template located in the child theme always wins: authors keep full
 * control by shipping a physical stub (e.g. for conditional controller
 * selection or Routes-loaded templates).
 */
class TemplateDispatcher {

	/**
	 * Hooks template_include after the theme hierarchy has resolved.
	 *
	 * @return void
	 */
	public function register(): void {
		\add_filter( 'template_include', [ $this, 'maybe_dispatch' ], 90 );
	}

	/**
	 * Redirects parent-theme template fallbacks to the controller dispatcher
	 * when a candidate resolves to a controller.
	 *
	 * @param string $template The template WordPress located.
	 *
	 * @return string
	 */
	public function maybe_dispatch( string $template ): string {

		if ( ! $this->is_parent_theme_template( $template ) ) {
			return $template;
		}

		if ( ControllerFactory::resolve_candidate() === null ) {
			return $template;
		}

		return \get_template_directory() . '/dispatch.php';
	}

	/**
	 * Whether a child theme is active and the located template is a parent
	 * theme fallback (dispatch never overrides a child theme's own files,
	 * and is inert without a child theme).
	 *
	 * @param string $template
	 *
	 * @return bool
	 */
	protected function is_parent_theme_template( string $template ): bool {
		return \get_stylesheet_directory() !== \get_template_directory()
			&& str_starts_with( $template, \get_template_directory() . '/' );
	}
}
