<?php

/**
 * The template for displaying custom taxonomy archive pages.
 *
 * Catch-all for taxonomies without a more specific template or a dispatched
 * child controller: TaxonomyController adds the current term to the archive
 * context and infers `taxonomy-{taxonomy}.twig` → `taxonomy.twig` →
 * `archive.twig`, mirroring the WordPress template hierarchy.
 */

use PressGang\Controllers\TaxonomyController;

PressGang\PressGang::render( controller: TaxonomyController::class );
