<?php

/**
 * The template for displaying the front page.
 *
 * @package PressGang
 */

use PressGang\Controllers\PageController;

PressGang\PressGang::render(
	controller: PageController::class, twig: 'front-page.twig'
);
