<?php

/**
 * Calculates a reading time for a given block of text
 *
 * @param string $text
 * @param bool $to_nearest_minute
 * @param int $speed
 *
 * @return string
 */
function reading_time( string $text, bool $to_nearest_minute = false, int $speed = 200 ): string {
	$words = str_word_count( strip_tags( $text ) );

	$seconds = 0;

	if ( $to_nearest_minute ) {
		$minutes = (int) floor( $words / $speed );
		$seconds = (int) floor( $words % $speed / ( $speed / 60 ) );
	} else {
		$minutes = (int) ceil( $words / $speed );
	}

	$est = sprintf( _n( "%d minute", "%d minutes", $minutes, THEMENAME ), $minutes );

	if ( $seconds ) {
		$est .= ',' . sprintf( _n( "%d second", "%d seconds", $seconds, THEMENAME ), $seconds );
	}

	return $est;
}

/**
 * Retrieves the primary PSR-4 namespace of the child theme.
 *
 * This method reads the child theme's composer.json file to extract the PSR-4
 * namespace, following the PSR-4 autoloading standard for PHP classes.
 *
 * It first checks if a THEMENAMESPACE constant is defined and returns its value if so. If not,
 * the method looks for the 'autoload.psr-4' key in the composer.json file of the child theme
 * and returns the first namespace found, which is typically the primary namespace
 * used by the child theme.
 *
 * @return string|null The primary PSR-4 namespace of the child theme if found, or null if not.
 */
function get_child_theme_namespace(): ?string {
	static $namespace = null;

	if ( $namespace !== null ) {
		return $namespace;
	}

	if ( defined( 'THEMENAMESPACE' ) ) {
		$namespace = THEMENAMESPACE;
	} else {
		$composer_json_path = \get_stylesheet_directory() . '/composer.json';
		if ( file_exists( $composer_json_path ) ) {
			$composer_json = file_get_contents( $composer_json_path );

			if ( $composer_json === false ) {
				return null;
			}

			$composer_config = json_decode( $composer_json, true );
			if ( isset( $composer_config['autoload']['psr-4'] ) && is_array( $composer_config['autoload']['psr-4'] ) ) {
				$namespaces = array_keys( $composer_config['autoload']['psr-4'] );
				$first_namespace = reset( $namespaces );

				if ( is_string( $first_namespace ) ) {
					$namespace = rtrim( $first_namespace, '\\' ); // Returns the first namespace, trimmed of trailing slashes
				}
			}
		}
	}

	return $namespace;
}
