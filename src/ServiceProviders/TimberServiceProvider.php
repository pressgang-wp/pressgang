<?php

namespace PressGang\ServiceProviders;

use PressGang\ContextManagers\AcfOptionsContextManager;
use PressGang\ContextManagers\ContextManagerInterface;
use PressGang\ContextManagers\SiteContextManager;
use PressGang\ContextManagers\MenuContextManager;
use PressGang\ContextManagers\ThemeModsContextManager;
use PressGang\ContextManagers\WooCommerceContextManager;
use PressGang\TwigExtensions\GeneralExtensionManager;
use PressGang\TwigExtensions\MetaDescriptionExtensionManager;
use PressGang\TwigExtensions\TwigExtensionManagerInterface;
use PressGang\TwigExtensions\WidgetExtensionManager;
use PressGang\TwigExtensions\WooCommerceExtensionManager;
use Twig\Environment;

/**
 * Class TimberServiceProvider
 *
 * Manages the setup and provisioning of services related to the Timber library in PressGang.
 *
 * It handles the registration of various context managers and Twig extension managers,
 * thereby enhancing the functionality of Timber's context and Twig environment.
 *
 * @package PressGang\ServiceProviders
 */
class TimberServiceProvider {
	protected array $context_managers = [];
	protected array $extension_managers = [];

	/**
	 * Bootstraps the service provider by registering context managers and Twig extension managers.
	 *
	 */
	public function boot(): void {

		$this->register_context_managers();
		$this->register_twig_extension_managers();

		// Add context filters
		\add_filter( 'timber/context', [ $this, 'add_to_context' ] );

		// Add to Twig functions
		\add_filter( 'timber/twig', [ $this, 'add_to_twig' ] );
	}

	/**
	 * Registers context managers for adding data to the Timber context.
	 *
	 * Adds various context managers to the internal collection.
	 *
	 * TODO should be defined in Config
	 */
	protected function register_context_managers(): void {
		$this->register_context_manager( new SiteContextManager() );
		$this->register_context_manager( new MenuContextManager() );
		$this->register_context_manager( new ThemeModsContextManager() );
		$this->register_context_manager( new AcfOptionsContextManager() );
		$this->register_context_manager( new WooCommerceContextManager() );
	}

	/**
	 * Registers Twig extension managers for adding functions and globals to the Twig environment.
	 *
	 * Adds various Twig extension managers to the internal collection.
	 *
	 * TODO should be defined in Config
	 */
	protected function register_twig_extension_managers(): void {
		$this->register_twig_extension_manager( new GeneralExtensionManager() );
		$this->register_twig_extension_manager( new MetaDescriptionExtensionManager() );
		$this->register_twig_extension_manager( new WidgetExtensionManager() );
		$this->register_twig_extension_manager( new WooCommerceExtensionManager() );
	}

	/**
	 * Registers a context manager for adding data to the Timber context.
	 *
	 * @param ContextManagerInterface $manager The context manager to be registered.
	 */
	protected function register_context_manager( ContextManagerInterface $manager ): void {
		$this->context_managers[] = $manager;
	}

	/**
	 * Registers a Twig extension manager.
	 *
	 * @param TwigExtensionManagerInterface $manager The Twig extension manager to be registered.
	 */
	protected function register_twig_extension_manager( TwigExtensionManagerInterface $manager ): void {
		$this->extension_managers[] = $manager;
	}

	/**
	 * Adds additional data to the Timber context using the registered context managers.
	 *
	 * Iterates through each registered context manager and applies its modifications to the Timber context.
	 *
	 * @param array $context The current Timber context array.
	 * @return array The modified Timber context array.
	 */
	public function add_to_context( array $context ): array {
		foreach ( $this->context_managers as $manager ) {
			$context = $manager->add_to_context( $context );
		}

		return $context;
	}

	/**
	 * Adds extensions to the Twig environment using the registered Twig extension managers.
	 *
	 * Iterates through each registered Twig extension manager and applies its additions of functions and globals to the Twig environment.
	 *
	 * @param Environment $twig The Twig environment.
	 * @return Environment The Twig environment with added extensions.
	 */
	public function add_to_twig( Environment $twig ): Environment {
		foreach ( $this->extension_managers as $manager ) {
			$manager->add_twig_functions( $twig );
			$manager->add_twig_globals( $twig );
		}

		return $twig;
	}
}
