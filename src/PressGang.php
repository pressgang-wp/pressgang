<?php

namespace PressGang;

use PressGang\Bootstrap\Loader;
use PressGang\Controllers\ControllerFactory;
use PressGang\ServiceProviders\TimberServiceProvider;
use Timber\Timber;

/**
 * Entry point for the PressGang theme framework. Instantiated in functions.php
 * with a Loader and TimberServiceProvider, then boot() initialises Timber,
 * loads config-driven components, and registers context managers and Twig extensions.
 */
class PressGang {

	/** @var Loader */
	private Loader $loader;

	/** @var TimberServiceProvider */
	private TimberServiceProvider $timberServiceProvider;

	/**
	 * @param Loader $loader
	 * @param TimberServiceProvider $timberServiceProvider
	 */
	public function __construct(Loader $loader, TimberServiceProvider $timberServiceProvider) {
		$this->loader = $loader;
		$this->timberServiceProvider = $timberServiceProvider;
	}

	/**
	 * Initialises Timber, loads config-driven components, and boots the service provider.
	 */
	public function boot(): void {
		// Initialize Timber
		Timber::init();

		// Initialize the Loader to load theme settings
		$this->loader->initialize();

		// Initialize the Timber service provider
		$this->timberServiceProvider->boot();
	}

	/**
	 * Convenience wrapper around ControllerFactory::render().
	 *
	 * @param string|null $template
	 * @param string|null $controller
	 * @param string|null $twig
	 */
	public static function render(?string $template = null, ?string $controller = null, ?string $twig = null): void {
		ControllerFactory::render($template, $controller, $twig);
	}
}
