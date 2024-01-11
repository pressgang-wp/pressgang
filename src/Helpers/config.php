<?php

/**
 * Get the specified configuration value.
 *
 * @param string $key The key of the configuration item.
 * @param mixed $default The default value to return if the configuration item doesn't exist.
 *
 * @return mixed
 */
function config( $key, $default = null ) {
	return PressGang\Bootstrap\Config::get( $key, $default );
}
