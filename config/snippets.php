<?php

/**
 * Snippets Configuration
 *
 * Defines the snippet classes to be included in the theme, along with their arguments. Each entry
 * in the array corresponds to a snippet class, with the key being either the fully qualified class
 * name (including namespace) or a name relative to the Snippets namespace.
 *
 * Resolution (via PressGang\Util\ClassResolver) tries, in order:
 *   1. a fully qualified name under the child or parent root, used as-is;
 *   2. {ChildNamespace}\Snippets\{name};
 *   3. PressGang\Snippets\{name}.
 *
 * The pressgang-snippets library groups snippets into sub-namespaces — Theme\,
 * Content\, Acf\, Integration\, Seo\, Facebook\, Google\, WooCommerce\ — so a
 * library snippet MUST be named with its sub-namespace ('Theme\DisableEmojis',
 * not 'DisableEmojis'). A name that resolves to nothing is skipped silently, so
 * verify with `wp capstan snippets` or `wp capstan doctor` after editing.
 *
 * Note: config files are merged by filename, so a child theme
 * `config/snippets.php` REPLACES this file. Re-declare any default below that
 * the child theme still wants.
 *
 * Example Configuration Format:
 * [
 *     'Fully\\Qualified\\Namespace\\SpecificSnippet' => ['arg1' => 'value1'],
 *     'Theme\\GeneralSnippet' => ['arg2' => 'value2'],
 *     ...
 * ]
 *
 * @var array
 */
return [
	'Theme\DisableEmojis' => [],
	'Theme\EditorStyles'  => [],
	'Seo\OpenGraph'       => [],
];
