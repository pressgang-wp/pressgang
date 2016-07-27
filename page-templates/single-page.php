<?php
/**
 * Template Name: Single Page Layout
 *
 * Add a single page layout by displaying children of a parent with this template on a single page
 *
 * @package WordPress
 * @subpackage Pressgang
 */

require_once __DIR__ . "/../controllers/single-page-controller.php";

// TODO filters for singe page permalinks are in pressgang/inc/filters.php and probably should be moved!

$controller = new Pressgang\SinglePageControllert('single-page.twig');
$controller->render();