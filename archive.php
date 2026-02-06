<?php

/**
 * The template for displaying archive pages.
 *
 * @package PressGang
 */

use PressGang\Controllers\PostsController;

PressGang\PressGang::render( controller: PostsController::class );
