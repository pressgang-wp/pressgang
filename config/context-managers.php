<?php

/**
 * Context Managers
 *
 * This configuration file defines the context managers used within the PressGang framework
 * to add data to the Timber context. Each context manager implements the ContextManagerInterface,
 * ensuring compatibility with the PressGang context management system. The context managers
 * are responsible for enriching the Timber context with various types of data such as site information,
 * menus, theme modifications, ACF options, and WooCommerce-related data.
 *
 * These context managers are dynamically loaded and registered within the TimberServiceProvider class,
 * allowing for flexible and extensible context management in Timber templates.
 *
 * Usage:
 * - Place this file in the child theme directory.
 * - Modify the TimberServiceProvider class to load context managers from this configuration file.
 * - Add or remove context managers as needed to customize the data available in the Timber context.
 *
 * @package PressGang\ContextManagers
 */
return [
	\PressGang\ContextManagers\SiteContextManager::class,
	\PressGang\ContextManagers\MenuContextManager::class,
	\PressGang\ContextManagers\ThemeModsContextManager::class,
	\PressGang\ContextManagers\AcfOptionsContextManager::class,
	\PressGang\ContextManagers\WooCommerceContextManager::class,
];
