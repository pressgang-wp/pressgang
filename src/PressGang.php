<?php

namespace PressGang;

use PressGang\Bootstrap\Config;
use PressGang\Bootstrap\Loader;
use PressGang\Controllers\ControllerFactory;
use PressGang\ServiceProviders\ServiceProviderInterface;
use Timber\Timber;

/**
 * Entry point for the PressGang theme framework. Instantiated in functions.php
 * with a Loader, then boot() initialises Timber, loads config-driven components,
 * and boots configured service providers.
 */
class PressGang {

	/** @var Loader */
	private Loader $loader;

	/**
	 * @param Loader $loader
	 */
	public function __construct( Loader $loader ) {
		$this->loader = $loader;
	}

	/**
	 * Initialises Timber, loads config-driven components, and boots service providers.
	 *
	 * @return void
	 */
	public function boot(): void {
		// Initialize Timber
		Timber::init();

		// Initialize the Loader to load theme settings
		$this->loader->initialize();

		// Boot service providers from config and filters.
		$this->boot_service_providers();
	}

	/**
	 * Boots service providers.
	 *
	 * Providers are configured as class strings in config/service-providers.php
	 * and can be extended via the pressgang_service_providers filter. Entries
	 * that are not loadable ServiceProviderInterface implementations are skipped.
	 *
	 * @return void
	 */
	protected function boot_service_providers(): void {
		$providers = Config::get( 'service-providers', [] );
		$providers = \apply_filters( 'pressgang_service_providers', $providers );

		foreach ( (array) $providers as $class ) {
			if ( is_string( $class ) && class_exists( $class ) ) {
				$provider = new $class();

				if ( $provider instanceof ServiceProviderInterface ) {
					$provider->boot();
				}
			}
		}
	}

	/**
	 * Convenience wrapper around ControllerFactory::render().
	 *
	 * @param string|null $template
	 * @param string|null $controller
	 * @param string|null $twig
	 */
	public static function render( ?string $template = null, ?string $controller = null, ?string $twig = null ): void {
		ControllerFactory::render( $template, $controller, $twig );
	}
}
