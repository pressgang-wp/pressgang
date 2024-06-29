<?php

namespace PressGang\ServiceProviders;

use PressGang\Bootstrap\Config;
use PressGang\ContextManagers\ContextManagerInterface;
use PressGang\TwigExtensions\TwigExtensionManagerInterface;
use Twig\Environment;

/**
 * Class TimberServiceProvider
 *
 * Manages the setup and provisioning of services related to the Timber library in PressGang.
 *
 * It handles the registration of various context managers and Twig extension managers,
 * thereby enhancing the functionality of Timber's context and Twig environment.
 *
 * @package PressGang\ServiceProviders
 */
class TimberServiceProvider {
	protected array $context_managers = [];
	protected array $twig_extensions = [];

	/**
	 * Bootstraps the service provider by registering context managers and Twig extension managers.
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
	 * Registers context managers for adding data to the Timber context.
	 *
	 * Adds various context managers to the internal collection.
	 */
	protected function register_context_managers(): void {
		foreach ( Config::get( 'context-managers' ) as $manager_class ) {
			if ( class_exists( $manager_class ) ) {
				$this->register_context_manager( new $manager_class() );
			}
		}
	}

	/**
	 * Registers Twig extension managers for adding functions and globals to the Twig environment.
	 *
	 * Adds various Twig extension managers to the internal collection.
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
	 * Adds additional data to the Timber context using the registered context managers.
	 *
	 * Iterates through each registered context manager and applies its modifications to the Timber context.
	 *
	 * @param array $context The current Timber context array.
	 *
	 * @return array The modified Timber context array.
	 */
	public function add_to_context( array $context ): array {
		foreach ( $this->context_managers as $manager ) {
			$context = $manager->add_to_context( $context );
		}

		return $context;
	}

	/**
	 * Adds extensions to the Twig environment using the registered Twig extension managers.
	 *
	 * Iterates through each registered Twig extension manager and applies its additions of functions and globals to the Twig environment.
	 *
	 * @param Environment $twig The Twig environment.
	 *
	 * @return Environment The Twig environment with added extensions.
	 */
	public function add_to_twig( Environment $twig ): Environment {
		foreach ( $this->twig_extensions as $manager ) {
			$manager->add_twig_functions( $twig );
			$manager->add_twig_globals( $twig );
		}

		return $twig;
	}

	/**
	 * Registers the template locations for PressGang Snippets in Timber.
	 *
	 * This function adds a custom path to the Timber template locations, allowing Timber to locate
	 * and use Twig templates stored in the PressGang Snippets repository. It dynamically constructs
	 * the path to the 'views' directory of the PressGang Snippets, located in the child theme's
	 * 'vendor' directory, and adds it to Timber's recognized paths.
	 *
	 * The function checks if the directory exists before adding it to Timber's paths to ensure
	 * that only valid directories are used, thereby preventing errors from non-existent paths.
	 *
	 * @return void
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
