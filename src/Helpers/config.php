<?php

/**
 * Get the specified configuration value.
 *
 * @param string $key The key of the configuration item.
 * @param mixed|null $default The default value to return if the configuration item doesn't exist.
 *
 * @return mixed
 */
function config( string $key, mixed $default = null ): mixed {
	return PressGang\Bootstrap\Config::get( $key, $default );
}
