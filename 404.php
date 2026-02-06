<?php

/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package PressGang
 */

use PressGang\Controllers\NotFoundController;

PressGang\PressGang::render( controller: NotFoundController::class );
