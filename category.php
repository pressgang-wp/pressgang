<?php

/**
 * The template for displaying category archive pages.
 *
 * @package PressGang
 */

use PressGang\Controllers\PostsController;

PressGang\PressGang::render( controller: PostsController::class );
