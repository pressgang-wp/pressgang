<?php

namespace PressGang\Templates;

/**
 * Observes and augments WordPress's template hierarchy resolution.
 *
 * Two responsibilities:
 *
 * 1. **Hyphenated template names** — for every hierarchy candidate containing
 *    an underscore (derived from post type / taxonomy keys, e.g.
 *    `taxonomy-event_type.php`), a hyphenated twin (`taxonomy-event-type.php`)
 *    is inserted ahead of it, so theme files can use consistent kebab-case
 *    naming. Underscored filenames continue to work.
 *
 * 2. **Candidate recording** — candidate slugs are recorded in resolution
 *    order (most specific first) for the current request, giving
 *    ControllerFactory the information to map templates to controllers
 *    without per-template PHP stubs.
 *
 * @see https://developer.wordpress.org/reference/hooks/type_template_hierarchy/
 */
class TemplateHierarchy {

	/**
	 * Template types WordPress resolves via {$type}_template_hierarchy.
	 *
	 * @var array<int, string>
	 */
	protected const TEMPLATE_TYPES = [
		'embed',
		'404',
		'search',
		'frontpage',
		'home',
		'privacypolicy',
		'taxonomy',
		'attachment',
		'single',
		'page',
		'singular',
		'category',
		'tag',
		'author',
		'date',
		'archive',
		'paged',
		'index',
	];

	/**
	 * Candidate template slugs recorded for the current request, most
	 * specific first.
	 *
	 * @var array<int, string>
	 */
	protected static array $candidates = [];

	/**
	 * Hooks every template hierarchy filter.
	 *
	 * @return void
	 */
	public function register(): void {
		foreach ( self::TEMPLATE_TYPES as $type ) {
			\add_filter( "{$type}_template_hierarchy", [ $this, 'filter_candidates' ] );
		}
	}

	/**
	 * Inserts hyphenated twins for underscored candidates and records all
	 * candidate slugs in order.
	 *
	 * @param array<int, string> $templates Candidate template filenames.
	 *
	 * @return array<int, string>
	 */
	public function filter_candidates( array $templates ): array {

		$templates = $this->with_hyphenated_variants( $templates );

		$this->record( $templates );

		return $templates;
	}

	/**
	 * Inserts a kebab-case twin ahead of each candidate containing an
	 * underscore.
	 *
	 * @param array<int, string> $templates Candidate template filenames.
	 *
	 * @return array<int, string>
	 */
	protected function with_hyphenated_variants( array $templates ): array {

		$augmented = [];

		foreach ( $templates as $template ) {
			$hyphenated = str_replace( '_', '-', $template );

			if ( $hyphenated !== $template ) {
				$augmented[] = $hyphenated;
			}

			$augmented[] = $template;
		}

		return $augmented;
	}

	/**
	 * Records candidate slugs, skipping path-qualified candidates (custom
	 * page template files) and duplicates.
	 *
	 * @param array<int, string> $templates Candidate template filenames.
	 *
	 * @return void
	 */
	protected function record( array $templates ): void {

		foreach ( $templates as $template ) {
			$slug = basename( $template, '.php' );

			if ( ! str_contains( $template, '/' ) && ! in_array( $slug, self::$candidates, true ) ) {
				self::$candidates[] = $slug;
			}
		}
	}

	/**
	 * The candidate slugs recorded for the current request, most specific first.
	 *
	 * @return array<int, string>
	 */
	public static function candidates(): array {
		return self::$candidates;
	}

	/**
	 * Prepends candidate slugs ahead of anything recorded so far.
	 *
	 * For code that knows the semantic template of the current request better
	 * than WordPress's conditionals — e.g. custom route handlers, where an
	 * empty paged listing would otherwise be flagged 404 and record no useful
	 * candidates.
	 *
	 * @param string ...$slugs Candidate slugs, most specific first.
	 *
	 * @return void
	 */
	public static function prepend( string ...$slugs ): void {
		self::$candidates = array_values( array_unique( [ ...$slugs, ...self::$candidates ] ) );
	}

	/**
	 * Clears recorded candidates (testing).
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$candidates = [];
	}
}
