<?php

use PressGang\Controllers\PageController;

PressGang\PressGang::render(
	controller: PageController::class, twig: 'front-page.twig'
);
