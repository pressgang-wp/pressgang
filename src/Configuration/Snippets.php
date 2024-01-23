<?php

namespace PressGang\Configuration;

use PressGang\Bootstrap\Config;
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
	 * This method reads the specified snippets configuration and dynamically loads and initializes
	 * the snippet classes. The configuration can specify each snippet by either a fully qualified
	 * class name or just a simple class name.
	 *
	 * If a fully qualified namespace is provided in the configuration, it uses that namespace directly.
	 * Otherwise, it attempts to guess the namespace: it first looks for the snippet in the child theme's
	 * \Snippets namespace and then in the PressGang\Snippets namespace.
	 *
	 * The configuration for each snippet should include the class name as the key (which can be a simple
	 * class name or a fully qualified class name) and an array of arguments as the value, which will be
	 * passed to the class's constructor.
	 *
	 * The snippets configuration is expected to be in the format:
	 * [
	 *     'Fully\\Qualified\\Namespace\\SnippetClassName' => ['arg1' => 'value1', 'arg2' => 'value2'],
	 *     'SimpleSnippetClassName' => ['arg3' => 'value3', 'arg4' => 'value4'],
	 *     ...
	 * ]
	 *
	 * @return void
	 */
	protected function include_snippets(): void {

		$child_theme_namespace = get_child_theme_namespace();

		foreach ( $this->config as $snippet => $args ) {
			// Check if a fully qualified namespace is provided in the config
			if ( str_contains( $snippet, '\\' ) ) {
				$class = $snippet;
			} else {
				// Guess the namespace if not provided
				$child_class  = $child_theme_namespace ? "$child_theme_namespace\\Snippets\\$snippet" : null;
				$parent_class = "PressGang\\Snippets\\$snippet";
				$class        = class_exists( $child_class ) ? $child_class : $parent_class;
			}

			if ( $class && in_array( SnippetInterface::class, class_implements( $class ) ) ) {
				new $class( $args );
			}
		}
	}
}
