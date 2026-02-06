<?php

namespace PressGang\Configuration;

/**
 * Registers custom query variables from config/query-vars.php via the query_vars
 * filter. Registered variables become available through get_query_var() in templates.
 *
 * Why: keeps query variable registration declarative and out of functions.php.
 * Extend via: child theme config override.
 */
class QueryVars extends ConfigurationSingleton {

	/**
	 * @var array Array of query variables to be registered.
	 */
	protected array $config;

	/**
	 *
	 * This method registers the custom query variables defined in the configuration array
	 * to the WordPress query system using the 'query_vars' filter.
	 *
	 * @param array $config An array of query variables to be added.
	 *                      Each element in the array should be a string representing a query variable name.
	 *
	 * @return void
	 */
	#[\Override]
	public function initialize( array $config ): void {

		$this->config = $config;

		\add_filter( 'query_vars', function ( $vars ) {
			foreach ( $this->config as $var ) {
				$vars[] = $var;
			}

			return $vars;
		} );
	}
}
