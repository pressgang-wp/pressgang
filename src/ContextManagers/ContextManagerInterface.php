<?php

namespace PressGang\ContextManagers;

/**
 * Contract for Timber context managers. Implementations enrich the global
 * Timber::context() with shared data (menus, site info, options, etc.) and are
 * registered via config/context-managers.php.
 */
interface ContextManagerInterface {

	/**
	 * Adds data to the Timber context.
	 *
	 * @param array<string, mixed> $context
	 *
	 * @return array<string, mixed>
	 */
	public function add_to_context( array $context ): array;
}
