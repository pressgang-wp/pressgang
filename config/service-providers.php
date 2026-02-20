<?php

/**
 * Service providers.
 *
 * Service provider class strings listed here are instantiated after PressGang
 * boot completes (after Timber init and Loader initialize).
 *
 * Child themes and plugins can also modify this list via the
 * pressgang_service_providers filter.
 *
 * Note: a child theme `config/service-providers.php` replaces the parent file.
 * Keep TimberServiceProvider in the child list unless you are intentionally
 * replacing the default Timber integration.
 */
return [
	\PressGang\ServiceProviders\TimberServiceProvider::class,
];
