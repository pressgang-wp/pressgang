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
class TimberServiceProvider implements ServiceProviderInterface {
	protected array $context_managers = [];
	protected array $twig_extensions = [];

	/**
	 * Registers all managers and hooks into timber/context and timber/twig filters.
	 */
	public function boot(): void {
		$this->register_context_managers();
		$this->register_twig_extension_managers();
		$this->register_snippets_template_locations();
		$this->register_twig_environment_options();

		// Add context filters
		\add_filter( 'timber/context', [ $this, 'add_to_context' ] );

		// Add to Twig functions
		\add_filter( 'timber/twig', [ $this, 'add_to_twig' ] );
	}

	/**
	 * Registers Twig environment options based on config/timber.php.
	 */
	protected function register_twig_environment_options(): void {
		\add_filter( 'timber/twig/environment/options', [ $this, 'add_twig_environment_options' ] );
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
	 * Applies configured Timber/Twig environment options.
	 *
	 * @param array<string, mixed> $options
	 *
	 * @return array<string, mixed>
	 */
	public function add_twig_environment_options( array $options ): array {
		$timber_config = Config::get( 'timber', [] );

		if ( ! is_array( $timber_config ) ) {
			return $options;
		}

		$twig_config = $timber_config['twig'] ?? null;

		if ( ! is_array( $twig_config ) ) {
			return $options;
		}

		if ( array_key_exists( 'cache_enabled', $twig_config ) ) {
			$cache_enabled = (bool) $twig_config['cache_enabled'];

			if ( $cache_enabled ) {
				$cache_path = $twig_config['cache_path'] ?? true;
				$options['cache'] = is_string( $cache_path ) && $cache_path !== '' ? $cache_path : true;
			} else {
				$options['cache'] = false;
			}
		}

		if ( array_key_exists( 'auto_reload', $twig_config ) ) {
			$options['auto_reload'] = (bool) $twig_config['auto_reload'];
		}

		if ( array_key_exists( 'debug', $twig_config ) ) {
			$options['debug'] = (bool) $twig_config['debug'];
		}

		return $options;
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
