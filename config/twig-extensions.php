<?php

/**
 * Twig Extension Managers
 *
 * This configuration file defines the Twig extension managers used within the PressGang framework
 * to add custom functions, filters, and globals to the Twig environment. Each Twig extension manager
 * implements the TwigExtensionManagerInterface, ensuring compatibility with the PressGang Twig extension
 * management system. The extension managers enhance the functionality of Twig templates by providing
 * additional tools and utilities.
 *
 * These Twig extension managers are dynamically loaded and registered within the TimberServiceProvider class,
 * allowing for flexible and extensible customization of the Twig environment in Timber templates.
 *
 * Usage:
 * - Place this file in the child theme directory.
 * - Modify the TimberServiceProvider class to load Twig extension managers from this configuration file.
 * - Add or remove extension managers as needed to customize the functionality available in Twig templates.
 *
 *
 * @package PressGang\TwigExtensions
 */
return [
	\PressGang\TwigExtensions\GeneralExtensionManager::class,
	\PressGang\TwigExtensions\MetaDescriptionExtensionManager::class,
	\PressGang\TwigExtensions\WidgetExtensionManager::class,
	\PressGang\TwigExtensions\WooCommerceExtensionManager::class,
];
