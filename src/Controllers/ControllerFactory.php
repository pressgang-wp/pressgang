<?php

namespace PressGang\Controllers;

use PressGang\Bootstrap\Config;
use PressGang\Templates\TemplateHierarchy;
use PressGang\Util\ClassResolver;

/**
 * Factory that resolves and renders PressGang controllers. Given a WordPress template
 * filename, it infers the appropriate controller class, instantiates it, and calls render().
 * Used by PressGang::render() as the primary entry point from template files.
 *
 * Controllers are resolved child-theme-first: a child theme may override any
 * controller (including the PostController fallback) simply by declaring a class
 * of the same name under its own \Controllers namespace — no config required.
 *
 * Extend via: child theme namespace override, or the
 * pressgang_{controller}_template and pressgang_{controller}_context filters.
 */
class ControllerFactory {

	/**
	 * Creates a controller instance, passing the template only if provided.
	 *
	 * @param string $controller_class
	 * @param string|null $twig_template
	 *
	 * @return ControllerInterface
	 */
	public static function make( string $controller_class, ?string $twig_template = null ): ControllerInterface {
		// Use the splat operator to unpack filtered arguments (removes null values)
		// i.e. Honours controller default template args.
		return new $controller_class( ...array_filter( [ $twig_template ] ) );
	}

	/**
	 * Infers a controller FQCN from a WP template filename, falling back to PostController.
	 *
	 * @param string $template
	 *
	 * @return string Fully qualified controller class name.
	 */
	public static function infer_controller_class( string $template ): string {
		return self::resolve_controller_class( $template, \get_child_theme_namespace() );
	}

	/**
	 * Resolves a controller FQCN for a template against a given child namespace.
	 *
	 * Pure resolution logic (no WordPress dependencies) so it can be unit-tested
	 * with an explicit namespace. Tries the template-inferred controller child-first,
	 * then the parent framework, then the PostController fallback (also overridable
	 * by the child theme).
	 *
	 * @param string      $template        WP template filename or slug.
	 * @param string|null $child_namespace Active child theme namespace, or null.
	 *
	 * @return string Fully qualified controller class name.
	 */
	public static function resolve_controller_class( string $template, ?string $child_namespace ): string {
		$base = self::to_studly_case( basename( $template, '.php' ) ) . 'Controller';

		// Try the inferred controller, child theme first then the framework.
		$resolved = ClassResolver::resolve( $base, 'Controllers', $child_namespace );

		if ( $resolved !== null ) {
			return $resolved;
		}

		// Fall back to PostController, still honouring a child theme override of it.
		return ClassResolver::resolve( 'PostController', 'Controllers', $child_namespace )
			?? PostController::class;
	}

	/**
	 * Converts a template slug (e.g. 'single-product') to StudlyCase (e.g. 'SingleProduct').
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected static function to_studly_case( string $string ): string {
		$string = str_replace( [ '-', '_' ], ' ', $string );
		$string = ucwords( $string );

		return str_replace( ' ', '', $string );
	}

	/**
	 * Resolves a controller and renders it. Infers the controller from the template if not given.
	 *
	 * @param string|null $template
	 * @param string|null $controller
	 * @param string|null $twig
	 */
	public static function render( ?string $template = null, ?string $controller = null, ?string $twig = null ): void {
		$controller = $controller ?? self::infer_controller_class( $template );

		$controller = self::make( $controller, $twig );
		$controller->render();
	}

	/**
	 * Resolves a controller from the current request's recorded template
	 * hierarchy candidates.
	 *
	 * @return array{controller: string, twig: string|null, candidate: string}|null
	 */
	public static function resolve_candidate(): ?array {
		return self::resolve_candidate_for(
			TemplateHierarchy::candidates(),
			(array) Config::get( 'controllers', [] ),
			\get_child_theme_namespace(),
			\PressGang\Configuration\PageTemplates::slugs()
		);
	}

	/**
	 * Resolves a controller from hierarchy candidates against a controller
	 * map and a child namespace.
	 *
	 * Pure resolution logic (no WordPress dependencies) so it can be
	 * unit-tested. For each candidate, most specific first:
	 *
	 * 1. an explicit config/controllers.php mapping wins;
	 * 2. otherwise a child-theme class matching the naming convention is
	 *    used — the literal `{ChildNS}\Controllers\{StudlyCandidate}Controller`
	 *    plus hierarchy-semantic inflections (see inferred_controller_names()).
	 *
	 * Parent framework controllers are deliberately not matched by
	 * convention here — the parent theme's own template files already route
	 * to them, so dispatching only activates for theme-defined behaviour.
	 *
	 * Registered page-template slugs additionally fall back to PageController
	 * (child-overridable) — the theme opted in by registering the template,
	 * so a default controller is intent, not hijacking.
	 *
	 * @param array<int, string>    $candidates           Candidate slugs, most specific first.
	 * @param array<string, string> $map                  Candidate slug => controller FQCN.
	 * @param string|null           $child_namespace      Active child theme namespace, or null.
	 * @param array<int, string>    $page_template_slugs  Registered page-template slugs.
	 *
	 * @return array{controller: string, twig: string|null, candidate: string}|null
	 */
	public static function resolve_candidate_for( array $candidates, array $map, ?string $child_namespace, array $page_template_slugs = [] ): ?array {

		foreach ( $candidates as $candidate ) {

			if ( isset( $map[ $candidate ] ) && \class_exists( $map[ $candidate ] ) ) {
				return [
					'controller' => $map[ $candidate ],
					'twig'       => self::candidate_twig( $candidate ),
					'candidate'  => $candidate,
				];
			}

			if ( $child_namespace ) {
				foreach ( self::inferred_controller_names( $candidate ) as $base ) {
					$class = "{$child_namespace}\\Controllers\\{$base}";

					if ( \class_exists( $class ) ) {
						return [
							'controller' => $class,
							'twig'       => self::candidate_twig( $candidate ),
							'candidate'  => $candidate,
						];
					}
				}
			}

			if ( in_array( $candidate, $page_template_slugs, true ) ) {
				return [
					'controller' => ClassResolver::resolve( 'PageController', 'Controllers', $child_namespace )
						?? PageController::class,
					'twig'       => self::candidate_twig( $candidate ),
					'candidate'  => $candidate,
				];
			}
		}

		return null;
	}

	/**
	 * Controller class basenames inferable from a hierarchy candidate, in
	 * priority order.
	 *
	 * Beyond the literal StudlyCase name, WordPress hierarchy semantics are
	 * applied: `archive-{type}` infers the pluralised type controller
	 * (`archive-event` => `EventsController`), and `single-{type}` /
	 * `taxonomy-{tax}` infer the bare StudlyCase subject
	 * (`single-event` => `EventController`, `taxonomy-event-type` =>
	 * `EventTypeController`).
	 *
	 * @param string $candidate Candidate slug.
	 *
	 * @return array<int, string>
	 */
	public static function inferred_controller_names( string $candidate ): array {

		$names = [ self::to_studly_case( $candidate ) . 'Controller' ];

		if ( \str_starts_with( $candidate, 'archive-' ) ) {
			static $inflector = null;
			$inflector ??= \Doctrine\Inflector\InflectorFactory::create()->build();

			$type    = self::to_studly_case( substr( $candidate, strlen( 'archive-' ) ) );
			$names[] = $inflector->pluralize( $type ) . 'Controller';
		}

		foreach ( [ 'single-', 'taxonomy-', 'category-', 'tag-' ] as $prefix ) {
			if ( \str_starts_with( $candidate, $prefix ) ) {
				$names[] = self::to_studly_case( substr( $candidate, strlen( $prefix ) ) ) . 'Controller';
			}
		}

		return array_values( array_unique( $names ) );
	}

	/**
	 * The candidate's Twig template, when the child theme has one — otherwise
	 * null so the controller's own template inference applies.
	 *
	 * @param string $candidate Candidate slug.
	 *
	 * @return string|null
	 */
	protected static function candidate_twig( string $candidate ): ?string {

		$twig = "{$candidate}.twig";

		return file_exists( \get_stylesheet_directory() . "/views/{$twig}" ) ? $twig : null;
	}

	/**
	 * Renders the controller resolved from the current request's hierarchy
	 * candidates. Entry point for the parent theme's dispatch.php.
	 *
	 * @return void
	 */
	public static function dispatch(): void {

		$resolved = self::resolve_candidate();

		if ( $resolved === null ) {
			// Nothing resolved (direct dispatch.php loads, e.g. custom
			// routes without a seeded candidate) — fall back to the posts
			// listing controller, which is safe on any query.
			$fallback = ClassResolver::resolve( 'PostsController', 'Controllers', \get_child_theme_namespace() )
				?? PostsController::class;

			self::render( controller: $fallback );

			return;
		}

		self::render( controller: $resolved['controller'], twig: $resolved['twig'] );
	}
}
