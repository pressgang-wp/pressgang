<?php

namespace PressGang\ServiceProviders;

use PressGang\Bootstrap\Config;
use PressGang\ContextManagers\ContextManagerInterface;
use PressGang\TwigExtensions\TwigExtensionManagerInterface;
use Twig\Environment;

/**
 * Bridges PressGang's config-driven context managers and Twig extensions into
 * Timber's filter system. Hooks into timber/context and timber/twig to apply all
 * registered managers, and adds the pressgang-snippets views directory to Timber's paths.
 */
class TimberServiceProvider {
	protected array $context_managers = [];
	protected array $twig_extensions = [];

	/**
	 * Registers all managers and hooks into timber/context and timber/twig filters.
	 */
	public function boot(): void {
		$this->register_context_managers();
		$this->register_twig_extension_managers();
		$this->register_snippets_template_locations();

		// Add context filters
		\add_filter( 'timber/context', [ $this, 'add_to_context' ] );

		// Add to Twig functions
		\add_filter( 'timber/twig', [ $this, 'add_to_twig' ] );
	}

	/**
	 * Instantiates context managers listed in config/context-managers.php.
	 */
	protected function register_context_managers(): void {
		foreach ( Config::get( 'context-managers' ) as $manager_class ) {
			if ( class_exists( $manager_class ) ) {
				$this->register_context_manager( new $manager_class() );
			}
		}
	}

	/**
	 * Instantiates Twig extension managers listed in config/twig-extensions.php.
	 */
	protected function register_twig_extension_managers(): void {
		foreach ( Config::get( 'twig-extensions' ) as $manager_class ) {
			if ( class_exists( $manager_class ) ) {
				$this->register_twig_extension_manager( new $manager_class() );
			}
		}
	}

	/**
	 * Registers a context manager for adding data to the Timber context.
	 *
	 * @param ContextManagerInterface $manager The context manager to be registered.
	 */
	protected function register_context_manager( ContextManagerInterface $manager ): void {
		$this->context_managers[] = $manager;
	}

	/**
	 * Registers a Twig extension manager.
	 *
	 * @param TwigExtensionManagerInterface $manager The Twig extension manager to be registered.
	 */
	protected function register_twig_extension_manager( TwigExtensionManagerInterface $manager ): void {
		$this->twig_extensions[] = $manager;
	}

	/**
	 * Applies all registered context managers to the Timber context.
	 *
	 * @param array<string, mixed> $context
	 *
	 * @return array<string, mixed>
	 */
	public function add_to_context( array $context ): array {
		foreach ( $this->context_managers as $manager ) {
			$context = $manager->add_to_context( $context );
		}

		return $context;
	}

	/**
	 * Applies all registered Twig extension managers to the Twig environment.
	 *
	 * @param Environment $twig
	 *
	 * @return Environment
	 */
	public function add_to_twig( Environment $twig ): Environment {
		foreach ( $this->twig_extensions as $manager ) {
			$manager->add_twig_functions( $twig );
			$manager->add_twig_filters( $twig );
			$manager->add_twig_globals( $twig );
		}

		return $twig;
	}

	/**
	 * Adds the pressgang-snippets vendor views directory to Timber's template paths.
	 */
	public function register_snippets_template_locations(): void {
		\add_filter( 'timber/locations', function ( $paths ) {
			$snippets_views_path = \get_stylesheet_directory() . '/vendor/pressgang-wp/pressgang-snippets/views';

			// Check if the directory exists before adding it to the paths
			if ( is_dir( $snippets_views_path ) ) {
				$paths[] = [ $snippets_views_path ];
			}

			return $paths;
		} );
	}
}
