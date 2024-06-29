<?php

namespace PressGang\Bootstrap;

/**
 * Interface ConfigLoaderInterface
 *
 * Defines the contract for configuration loaders in the application.
 * Implementations of this interface are responsible for loading configuration settings,
 * typically from various sources like files, databases, or external services.
 */
interface ConfigLoaderInterface {

	/**
	 * Loads configuration settings.
	 *
	 * This method should retrieve an array of configuration settings from the implemented source.
	 * The exact source and method of loading these settings are determined by the implementing class.
	 *
	 * @return array An associative array of configuration settings.
	 */
	public function load(): array;
}
