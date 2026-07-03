<?php

/**
 * Controller dispatcher.
 *
 * TemplateDispatcher redirects here when a parent-theme template fallback
 * resolves to a theme controller via config/controllers.php or the child
 * theme's \Controllers naming convention.
 *
 * @package PressGang
 */

use PressGang\Controllers\ControllerFactory;

ControllerFactory::dispatch();
