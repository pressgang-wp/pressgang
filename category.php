<?php

/**
 * The template for displaying category archive pages.
 *
 * Categories are taxonomy archives: TaxonomyController adds the current term
 * to the archive context and infers `category.twig` with an `archive.twig`
 * fallback, mirroring the WordPress template hierarchy.
 */

use PressGang\Controllers\TaxonomyController;

PressGang\PressGang::render( controller: TaxonomyController::class );
