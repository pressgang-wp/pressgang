<?php

use PressGang\Controllers\WooCommerce\WooCommerceControllerResolver;

PressGang\PressGang::render( controller: WooCommerceControllerResolver::resolve() );
