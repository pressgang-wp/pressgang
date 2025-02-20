<?php

namespace PressGang\Configuration;

use PressGang\Snippets\SnippetInterface;

class Snippets extends ConfigurationSingleton {
	/**
	 * @param array $config
	 *
	 * @return void
	 */
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
			$class = $this->resolve_class_namespace( $snippet, $child_theme_namespace );

			if ( $class && class_exists( $class ) && in_array( SnippetInterface::class, class_implements( $class ) ) ) {
				new $class( $args );
			}
		}
	}

	/**
	 * Resolves the full namespace for a given snippet class.
	 *
	 * Supports:
	 * - Fully qualified class names
	 * - Nested subfolders (e.g., WooCommerce\ProductColorSwatch)
	 * - Child theme overrides
	 *
	 * @param string $snippet The class name from the config.
	 * @param string|null $child_theme_namespace The child theme's namespace.
	 *
	 * @return string|null Fully resolved class name or null if not found.
	 */
	private function resolve_class_namespace( string $snippet, ?string $child_theme_namespace ): ?string {
		$parent_namespace = "PressGang\\Snippets\\"; // Default parent theme namespace

		// Check if the snippet is already fully qualified (starts with PressGang\ or the detected child theme namespace)
		if ( str_starts_with( $snippet, "PressGang\\" ) || ( $child_theme_namespace && str_starts_with( $snippet, $child_theme_namespace ) ) ) {
			return class_exists( $snippet ) ? $snippet : null;
		}

		// Try child theme first if a child theme namespace exists
		if ( $child_theme_namespace ) {
			$child_class = "$child_theme_namespace\\Snippets\\$snippet";
			if ( class_exists( $child_class ) ) {
				return $child_class;
			}
		}

		// Prepend the parent namespace (handles subfolders like WooCommerce\ProductColorSwatch)
		$parent_class = $parent_namespace . $snippet;
		if ( class_exists( $parent_class ) ) {
			return $parent_class;
		}

		return null; // Class not found
	}
}
