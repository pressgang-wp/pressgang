<?php

namespace PressGang\ServiceProviders;

use PressGang\ContextManagers\AcfOptionsContextManager;
use PressGang\ContextManagers\ContextManagerInterface;
use PressGang\ContextManagers\GeneralContextManager;
use PressGang\ContextManagers\MenuContextManager;
use PressGang\ContextManagers\ThemeModsContextManager;
use PressGang\ContextManagers\WooCommerceContextManager;
use PressGang\Twig\TwigExtensionManager;

/**
 * Class TimberServiceProvider
 *
 * Manages the setup and provisioning of services related to the Timber library in a WordPress theme.
 * It handles the registration of various context managers and adds additional functionalities to the Twig environment.
 *
 * @package PressGang\ServiceProviders
 */
class TimberServiceProvider {
	protected array $contextManagers = [];

	/**
	 * Bootstraps the service provider by registering context managers and configuring Timber.
	 *
	 * This method sets up various managers to add data to the Timber context and configures Twig extensions.
	 */
	public function boot(): void {
		// Register context managers
		$this->register_context_manager( new GeneralContextManager() );
		$this->register_context_manager( new MenuContextManager() );

		// Register Theme Mods
		$this->register_context_manager( new ThemeModsContextManager() );

		// Register ACF Options
		if ( function_exists( 'get_fields' ) && config( 'acf-options' ) ) {
			$this->register_context_manager( new AcfOptionsContextManager() );
		}

		// Register WooCommerce
		if ( class_exists( 'WooCommerce' ) ) {
			$this->register_context_manager( new WooCommerceContextManager() );
		}

		// Add context filters
		\add_filter( 'timber/context', [ $this, 'add_to_context' ] );

		// Add to Twig functions
		\add_filter( 'timber/twig', function ( $twig ) {
			$twigExtensionsManager = new TwigExtensionManager();

			return $twigExtensionsManager->addToTwig( $twig );
		} );
	}

	/**
	 * Registers a context manager for adding data to the Timber context.
	 *
	 * @param ContextManagerInterface $manager The context manager to be registered.
	 */
	protected function register_context_manager( ContextManagerInterface $manager ): void {
		$this->contextManagers[] = $manager;
	}

	/**
	 * Adds additional data to the Timber context using the registered context managers.
	 *
	 * Iterates through each registered context manager and applies its modifications to the Timber context.
	 *
	 * @param array $context The current Timber context array.
	 *
	 * @return array The modified Timber context array.
	 */
	public function add_to_context( $context ) {
		foreach ( $this->contextManagers as $manager ) {
			$context = $manager->add_to_context( $context );
		}

		return $context;
	}
}
