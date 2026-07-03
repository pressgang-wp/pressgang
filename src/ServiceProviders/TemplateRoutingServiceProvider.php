<?php

namespace PressGang\ServiceProviders;

use PressGang\Templates\TemplateDispatcher;
use PressGang\Templates\TemplateHierarchy;

/**
 * Opt-in convention-based template routing.
 *
 * Registers the template hierarchy observer (hyphenated candidate twins +
 * candidate recording) and the controller dispatcher (stub-free routing via
 * config/controllers.php and the {Studly}Controller convention).
 *
 * Enable by listing this provider in the child theme's
 * config/service-providers.php. Deliberately not a parent default: themes
 * built on explicit template stubs upgrade the framework without any change
 * in routing behaviour. Required for config/page-templates.php (file-less
 * page templates).
 */
class TemplateRoutingServiceProvider implements ServiceProviderInterface {

	/**
	 * Registers the hierarchy observer and dispatcher.
	 *
	 * @return void
	 */
	#[\Override]
	public function boot(): void {
		( new TemplateHierarchy() )->register();
		( new TemplateDispatcher() )->register();
	}
}
