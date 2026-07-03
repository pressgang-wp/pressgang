<?php

namespace PressGang\Configuration;

use PressGang\Templates\TemplateHierarchy;

/**
 * Registers file-less page templates from config/page-templates.php and routes
 * them through candidate-based controller dispatch — no per-template PHP stub
 * files required.
 *
 * Config maps a template id to its admin-facing name. Use legacy file-shaped
 * ids (e.g. 'page-templates/contact-page.php') when migrating a theme whose
 * pages already store that value in _wp_page_template — assignments and the
 * admin dropdown then carry over without a data migration. New themes can use
 * bare slugs ('contact-page').
 *
 *     return [
 *         'page-templates/contact-page.php' => 'Contact Page',
 *         'grid-page'                       => 'Grid Page',
 *     ];
 *
 * At request time the assigned template's slug (basename without .php) is
 * seeded as the leading hierarchy candidate, so the dispatcher resolves
 * `{Slug}Controller` from the child theme by convention (e.g. `sidebar-page`
 * => SidebarPageController) — falling back to PageController — and renders
 * `{slug}.twig`.
 *
 * Requires TemplateRoutingServiceProvider in config/service-providers.php —
 * the dispatcher is what renders the resolved controller.
 *
 * @see https://developer.wordpress.org/reference/hooks/theme_page_templates/
 */
class PageTemplates extends ConfigurationSingleton {

	/**
	 * Registered template slugs (basename without .php).
	 *
	 * @var array<int, string>
	 */
	protected static array $slugs = [];

	/**
	 * @param array<string, string> $config Template id => admin-facing name.
	 */
	#[\Override]
	public function initialize( array $config ): void {
		$this->config = $config;

		self::$slugs = array_values( array_map(
			static fn( string $id ): string => basename( $id, '.php' ),
			array_keys( $config )
		) );

		\add_filter( 'theme_page_templates', [ $this, 'register_templates' ] );
		\add_filter( 'page_template_hierarchy', [ $this, 'seed_candidate' ] );
	}

	/**
	 * Adds the configured templates to the admin template dropdown.
	 *
	 * @param array<string, string> $templates Registered page templates.
	 *
	 * @return array<string, string>
	 */
	public function register_templates( array $templates ): array {
		return array_merge( $templates, $this->config );
	}

	/**
	 * Seeds the assigned template's slug as the leading hierarchy candidate.
	 *
	 * Hooked to page_template_hierarchy purely for its timing (it fires
	 * exactly when WordPress resolves a page template); the candidates pass
	 * through unchanged.
	 *
	 * @param array<int, string> $candidates Page template hierarchy candidates.
	 *
	 * @return array<int, string>
	 */
	public function seed_candidate( array $candidates ): array {

		$assigned = \get_page_template_slug();

		if ( $assigned && isset( $this->config[ $assigned ] ) ) {
			TemplateHierarchy::prepend( basename( $assigned, '.php' ) );
		}

		return $candidates;
	}

	/**
	 * The registered template slugs — candidates that may fall back to
	 * PageController when no convention-named controller exists.
	 *
	 * @return array<int, string>
	 */
	public static function slugs(): array {
		return self::$slugs;
	}
}
