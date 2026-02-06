<?php

/**
 * The template for displaying author archive pages.
 *
 * @package PressGang
 */

use PressGang\Controllers\AuthorController;

PressGang\PressGang::render( controller: AuthorController::class );
