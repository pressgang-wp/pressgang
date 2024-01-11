<?php

namespace PressGang\Configuration;

/**
 * Class Plugins
 *
 * Manages the checking of required plugins for the WordPress theme.
 * It displays an admin warning if certain required plugins are not active.
 *
 * @package PressGang
 */
class Plugins extends ConfigurationSingleton {

	/**
	 * Initializes the Plugins class with configuration data.
	 *
	 * Sets up the configuration for required plugins and adds a filter to check if these plugins are active.
	 *
	 * @param array $config The configuration array for required plugins.
	 */
	public function initialize( $config ) {
		$this->config = $config;
		\add_filter( 'admin_init', [ $this, 'check_plugins_active' ] );
	}

	/**
	 * Checks if required plugins are active and displays an admin warning if not.
	 *
	 * Iterates through the configuration array and checks each plugin's active status.
	 * Displays an admin warning for any plugin that is not active.
	 */
	public function check_plugins_active() {
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
