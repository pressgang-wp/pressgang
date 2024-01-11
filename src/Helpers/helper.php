<?php

/**
 * Calculates a reading time for a given block of text
 *
 * @param $text
 * @param bool $to_nearest_minute
 *
 * @return string
 */
function reading_time( $text, $to_nearest_minute = false, $speed = 200 ) {
	$words = str_word_count( strip_tags( $text ) );

	$seconds = 0;

	if ( $to_nearest_minute ) {
		$minutes = floor( $words / $speed );
		$seconds = floor( $words % $speed / ( $speed / 60 ) );
	} else {
		$minutes = ceil( $words / $speed );
	}

	$est = sprintf( _n( "%d minute", "%d minutes", $minutes, THEMENAME ), $minutes );

	if ( $seconds ) {
		$est .= ',' . sprintf( _n( "%d second", "%d seconds", $seconds, THEMENAME ), $seconds );
	}

	return $est;
}