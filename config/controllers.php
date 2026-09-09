<?php

/**
 * Controllers Configuration
 *
 * Maps WordPress template hierarchy candidates (filenames without .php,
 * hyphenated variants included) to controller classes, so child themes can
 * route requests without per-template PHP stub files.
 *
 * Candidates are matched most specific first. Most themes need NO entries
 * here: unmapped candidates resolve by convention to
 * `{ChildNamespace}\Controllers\{Studly}Controller` (`search` =>
 * `SearchController`, `front-page` => `FrontPageController`), with
 * hierarchy-semantic inflections — `archive-{type}` => pluralised
 * `{Types}Controller` (`archive-event` => `EventsController`) and
 * `single-{type}` / `taxonomy-{tax}` => `{Subject}Controller`. Use explicit
 * entries when a controller name or Twig path defies convention. A physical template
 * file in the child theme always takes precedence over this mapping.
 *
 * Entries may be a controller class or an array with controller and template
 * keys. An explicit template overrides candidate Twig discovery; omit it to
 * retain discovery and constructor defaults. Use parent controllers when
 * only the Twig path differs, rather than creating constructor-only classes.
 *
 * Example:
 *
 *     return [
 *         'front-page'         => \MyTheme\Controllers\FrontPageController::class,
 *         'archive-event'      => \MyTheme\Controllers\EventsController::class,
 *         'taxonomy-event-type' => \MyTheme\Controllers\EventTypeController::class,
 *         'research-subpage' => [
 *             'controller' => \PressGang\Controllers\PageController::class,
 *             'template'   => 'page/research-subpage.twig',
 *         ],
 *         'home'               => \PressGang\Controllers\PostsController::class,
 *     ];
 *
 * @var array
 */
return [];
