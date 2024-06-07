<?php

namespace PressGang;

use PressGang\Bootstrap\Loader;
use PressGang\Controllers\ControllerFactory;
use PressGang\ServiceProviders\TimberServiceProvider;
use Timber\Timber;

/**
 * Class PressGang
 *
 * The foundational class for the PressGang theme. It handles the initialization of the theme components
 * and provides a static method for rendering pages using controllers.
 *
 * @package PressGang
 */
class PressGang {

	/**
	 * @var Loader
	 */
	private Loader $loader;

	/**
	 * @var TimberServiceProvider
	 */
	private TimberServiceProvider $timberServiceProvider;

	/**
	 * PressGang constructor.
	 *
	 * @param Loader $loader
	 * @param TimberServiceProvider $timberServiceProvider
	 */
	public function __construct(Loader $loader, TimberServiceProvider $timberServiceProvider) {
		$this->loader = $loader;
		$this->timberServiceProvider = $timberServiceProvider;
	}

	/**
	 * Boot method to initialize theme components.
	 *
	 * This method is responsible for setting up various components of the theme, such as Timber,
	 * service providers, and other necessary initializations for the theme to function correctly.
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
	 * Static render method to handle page rendering.
	 *
	 * This method is a convenience wrapper around the ControllerFactory's render method.
	 * It allows for easy rendering of pages using the specified parameters.
	 *
	 * @param string|null $template
	 * @param string|null $controller
	 * @param string|null $twig
	 */
	public static function render(?string $template = null, ?string $controller = null, ?string $twig = null): void {
		ControllerFactory::render($template, $controller, $twig);
	}
}
