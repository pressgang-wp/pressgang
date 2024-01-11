<?php

namespace PressGang;

use PressGang\Bootstrap\FileConfigLoader;
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
	 * Boot method to initialize theme components.
	 *
	 * This method is responsible for setting up various components of the theme, such as Timber,
	 * service providers, and other necessary initializations for the theme to function correctly.
	 */
	public function boot() {
		// Initialize Timber
		Timber::init();

		// Initialize the Timber service provider
		$timberServiceProvider = new TimberServiceProvider();
		$timberServiceProvider->boot();

		// Initialize the Loader to load theme settings
		new Loader( new FileConfigLoader() );
	}

	/**
	 * Static render method to handle page rendering.
	 *
	 * This method is a convenience wrapper around the ControllerFactory's render method.
	 * It allows for easy rendering of pages using the specified parameters.
	 *
	 * @param array $params An associative array of parameters which can include the template, controller class, and twig template.
	 */
	public static function render( array $params = [] ) {
		ControllerFactory::render( $params );
	}
}
