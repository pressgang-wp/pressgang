<?php

namespace PressGang\Configuration;

/**
 * Checks that required plugins listed in config/plugins.php are active. Displays
 * an admin notice for each missing plugin, prompting the site administrator to install it.
 *
 * Why: ensures theme dependencies are visible and actionable in the admin dashboard.
 * Extend via: child theme config override.
 */
class Plugins extends ConfigurationSingleton {

	/**
	 * Initializes the Plugins class with configuration data.
	 *
	 * Sets up the configuration for required plugins and adds a filter to check if these plugins are active.
	 *
	 * @param array $config The configuration array for required plugins.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_action( 'admin_init', [ $this, 'check_plugins_active' ] );
	}

	/**
	 * Checks if required plugins are active and displays an admin warning if not.
	 *
	 * Iterates through the configuration array and checks each plugin's active status.
	 * Displays an admin warning for any plugin that is not active.
	 */
	public function check_plugins_active(): void {
		foreach ( $this->config as $plugin => $message ) {
			if ( ! \is_plugin_active( $plugin ) ) {

				$message = $message ?: sprintf( _x( "%s not activated. Make sure you activate the plugin to use %s", 'Admin', THEMENAME ), $plugin, THEMENAME );

				\add_action( 'admin_notices', function () use ( $message, $plugin ) { ?>
						<div class="error">
							<p>
								<?php echo \esc_html( $message ); ?>
								<a href="<?php echo \esc_url( \admin_url( "plugins.php#{$plugin}" ) ); ?>">
									<?php echo \esc_url( \admin_url( "plugins.php" ) ); ?>
								</a>.
							</p>
						</div><?php
					}
				);
			}
		}
	}
}
