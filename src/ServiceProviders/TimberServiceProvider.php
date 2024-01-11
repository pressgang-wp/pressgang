<?php

namespace PressGang\ServiceProviders;

use PressGang\ContextManagers\AcfOptionsContextManager;
use PressGang\ContextManagers\ContextManagerInterface;
use PressGang\ContextManagers\GeneralContextManager;
use PressGang\ContextManagers\MenuContextManager;
use PressGang\ContextManagers\ThemeModsContextManager;
use PressGang\ContextManagers\WooCommerceContextManager;
use PressGang\Twig\TwigExtensionManager;

class TimberServiceProvider {
	protected $contextManagers = [];

	public function boot() {
		// Register context managers
		$this->registerContextManager( new GeneralContextManager() );
		$this->registerContextManager( new MenuContextManager() );

		// Register Theme Mods
		$this->registerContextManager( new ThemeModsContextManager() );

		// Register ACF Options
		if ( function_exists( 'get_fields' ) && config( 'acf-options' ) ) {
			$this->registerContextManager( new AcfOptionsContextManager() );
		}

		// Register WooCommerce
		if ( class_exists( 'WooCommerce' ) ) {
			$this->registerContextManager( new WooCommerceContextManager() );
		}

		// Add context filters
		add_filter( 'timber/context', [ $this, 'addToContext' ] );

		// Add to Twig functions
		add_filter( 'timber/twig', function ( $twig ) {
			$twigExtensionsManager = new TwigExtensionManager();

			return $twigExtensionsManager->addToTwig( $twig );
		} );
	}

	protected function registerContextManager( ContextManagerInterface $manager ) {
		$this->contextManagers[] = $manager;
	}

	public function addToContext( $context ) {
		foreach ( $this->contextManagers as $manager ) {
			$context = $manager->addToContext( $context );
		}

		return $context;
	}
}
