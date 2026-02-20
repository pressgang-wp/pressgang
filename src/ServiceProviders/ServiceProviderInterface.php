<?php

namespace PressGang\ServiceProviders;

/**
 * Contract for config-driven service providers.
 *
 * Classes listed in config/service-providers.php must implement this interface.
 * PressGang instantiates each class (no constructor args) and calls boot()
 * after Timber and the Loader have initialised.
 */
interface ServiceProviderInterface {

	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function boot(): void;
}
