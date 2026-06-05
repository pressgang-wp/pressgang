<?php

namespace PressGang\ServiceProviders;

use PressGang\SEO\MetaDescriptionService;

/**
 * Registers PressGang's fallback SEO meta description tag on wp_head.
 *
 * Why: keeps meta output in the WordPress hook system (where SEO plugins live)
 * rather than hardcoded in Twig, and suppresses PressGang's tag when a dedicated
 * SEO plugin already owns metadata — avoiding duplicate description tags.
 * Extend via: pressgang_should_render_meta_description / pressgang_has_seo_plugin
 * / pressgang_meta_description_priority filters, or a child-class override.
 */
class SeoServiceProvider implements ServiceProviderInterface {

	/**
	 * Register the meta description on wp_head unless an SEO plugin owns it.
	 */
	public function boot(): void {
		if ( ! $this->should_render_meta_description() ) {
			return;
		}

		\add_action(
			'wp_head',
			[ $this, 'render_meta_description' ],
			(int) \apply_filters( 'pressgang_meta_description_priority', 5 )
		);
	}

	/**
	 * Render the fallback meta description tag.
	 */
	public function render_meta_description(): void {
		$description = MetaDescriptionService::get_meta_description();

		if ( $description === '' ) {
			return;
		}

		printf(
			'<meta name="description" content="%s">' . "\n",
			\esc_attr( $description )
		);
	}

	/**
	 * Whether PressGang should output its fallback meta description tag.
	 *
	 * Production: false when a dedicated SEO plugin owns metadata.
	 *
	 * @see Filter: pressgang_should_render_meta_description
	 *
	 * @return bool
	 */
	protected function should_render_meta_description(): bool {
		return (bool) \apply_filters(
			'pressgang_should_render_meta_description',
			! $this->has_seo_plugin()
		);
	}

	/**
	 * Whether a dedicated SEO plugin is active and likely owns metadata output.
	 *
	 * Production: checks the Yoast / Rank Math / All in One SEO version constants.
	 *
	 * @see Filter: pressgang_has_seo_plugin
	 *
	 * @return bool
	 */
	protected function has_seo_plugin(): bool {
		return (bool) \apply_filters(
			'pressgang_has_seo_plugin',
			\defined( 'WPSEO_VERSION' )
			|| \defined( 'RANK_MATH_VERSION' )
			|| \defined( 'AIOSEO_VERSION' )
		);
	}
}
