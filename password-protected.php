<?php

/**
 * The template for displaying password-protected content.
 *
 * @package PressGang
 */

use PressGang\Controllers\PageController;

PressGang\PressGang::render( controller: PageController::class, twig: 'password-protected.twig' );
