<?php

/**
 * Snippets Configuration
 *
 * Defines the snippet classes to be included in the theme, along with their arguments. Each entry
 * in the array corresponds to a snippet class, with the key being either the fully qualified class
 * name (including namespace) or just the simple class name (if it follows the PressGang or child theme
 * Snippets namespace convention).
 *
 * When specifying a simple class name, the loader attempts to load the class from the child theme's
 * \Snippets namespace first and then from the PressGang\Snippets namespace. If a fully qualified class
 * name is provided, it directly uses that namespace.
 *
 * The configuration for each snippet should include the class name as the key and an array of arguments
 * as the value, which will be passed to the class's constructor.
 *
 * Example Configuration Format:
 * [
 *     'Fully\\Qualified\\Namespace\\SpecificSnippet' => ['arg1' => 'value1'],
 *     'GeneralSnippet' => ['arg2' => 'value2'],
 *     ...
 * ]
 *
 * Note: The loader fails silently if a specified class does not exist.
 *
 * @var array
 */
return [
	'DisableEmojis' => [],
	'OpenGraph'     => [],
	'EditorStyles'  => [],
];
