<?php

namespace PressGang\Configuration;

/**
 * Class QueryVars
 *
 * Registers custom query variables based on a configuration array.
 *
 * This class allows the addition of custom query variables to the WordPress query
 * system through a configuration array.
 *
 * @package PressGang\Configuration
 */
class QueryVars extends ConfigurationSingleton {

	/**
	 * @var array Array of query variables to be registered.
	 */
	protected array $config;

	/**
	 * This method registers the custom query variables defined in the configuration array
	 * to the WordPress query system using the 'query_vars' filter.
	 *
	 * @param array $config An array of query variables to be added.
	 *                      Each element in the array should be a string representing a query variable name.
	 * @return void
	 */
	public function initialize( array $config ): void {
		\add_filter('query_vars', function($vars) {
			foreach ($this->config as $var) {
				$vars[] = $var;
			}
			return $vars;
		});
	}
}
