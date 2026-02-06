<?php

/**
 * The main template file.
 *
 * @package PressGang
 */

use PressGang\Controllers\PostsController;

PressGang\PressGang::render( controller: PostsController::class );
