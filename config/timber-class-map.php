<?php

/**
 * Configuration file for Timber class mapping.
 *
 * This file returns an array that defines custom class mappings for various
 * WordPress elements within the Timber framework. The array keys represent
 * the type of WordPress element (e.g., 'post', 'term', 'comment'), and the
 * values are associative arrays where the keys represent the specific
 * post type, taxonomy, or other identifier, and the values are the fully
 * qualified class names that Timber should use for those elements.
 *
 * @link https://timber.github.io/docs/v2/guides/class-maps/
 *
 * Example structure:
 * 'post' => [
 *      'post' => \YourNamespace\PostClass::class,
 *      'custom_post_type' => \YourNamespace\CustomPostClass::class,
 * ] // This maps the 'post' post type and a custom post type to custom classes.
 *
 * 'term' => [
 *      'category' => \YourNamespace\CategoryClass::class,
 *      'custom_taxonomy' => \YourNamespace\CustomTermClass::class,
 * ] // This maps the 'category' taxonomy and a custom taxonomy to custom classes.
 *
 * 'comment' => [
 *      'default' => \YourNamespace\CommentClass::class,
 * ] // This maps the default comment type to a custom class.
 *
 * 'menu' => [
 *      'primary' => \YourNamespace\PrimaryMenuClass::class,
 *  ] // This maps the 'primary' menu to a custom class.
 *
 * 'menuitem' => [
 *      'default' => \YourNamespace\MenuItemClass::class,
 * ] // This maps the default menu item to a custom class.
 *
 * 'pages_menu' => [
 *      'default' => \YourNamespace\PagesMenuClass::class,
 * ] // This maps the default pages menu to a custom class.
 *
 * 'user' => [
 *      'administrator' => \YourNamespace\AdminUserClass::class,
 *      'subscriber' => \YourNamespace\SubscriberUserClass::class,
 * ] // This maps the 'administrator' and 'subscriber' user roles to custom classes.
 * 
 * This configuration will be utilized by the `TimberClassMap` class to
 * register the appropriate class mappings with Timber.
 *
 * @return array The configuration array for Timber class mapping.
 */
return [
	'post' => [
		'post' => PressGang\Post::class
	]
];
