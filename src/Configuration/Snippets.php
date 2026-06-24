<?php

namespace PressGang\Configuration;

use PressGang\Snippets\SnippetInterface;
use PressGang\Util\ClassResolver;

/**
 * Includes and initialises snippet classes from config/snippets.php. Resolves
 * snippet classes from child theme, parent theme, and fully qualified namespaces,
 * then instantiates each SnippetInterface implementation.
 *
 * Why: provides a declarative way to register reusable UI components (snippets).
 * Extend via: child theme config override or child theme namespace overrides.
 */
class Snippets extends ConfigurationSingleton {
	/**
	 * @param array $config
	 *
	 * @return void
	 */
	#[\Override]
	public function initialize( array $config ): void {
		$this->config = $config;
		$this->include_snippets();
	}

	/**
	 * Includes and initializes snippet classes based on the configuration file (e.g., snippets.php).
	 *
	 * This method dynamically loads snippet classes based on the provided configuration. It supports:
	 * - Fully qualified namespaces
	 * - Nested namespaces (e.g., WooCommerce\ProductColorSwatch)
	 * - Default fallback namespaces (PressGang\Snippets)
	 *
	 * The snippets configuration is expected to be in the format:
	 * [
	 *     'WooCommerce\ProductColorSwatch' => [],
	 *     'SimpleSnippetClassName' => ['arg1' => 'value1'],
	 *     'Fully\\Qualified\\Namespace\\SnippetClassName' => ['arg2' => 'value2']
	 * ]
	 *
	 * @return void
	 */
	protected function include_snippets(): void {
		$child_theme_namespace = get_child_theme_namespace();

		foreach ( $this->config as $snippet => $args ) {
			$class = ClassResolver::resolve( $snippet, 'Snippets', $child_theme_namespace );

			if ( $class && in_array( SnippetInterface::class, class_implements( $class ) ) ) {
				new $class( $args );
			}
		}
	}
}
