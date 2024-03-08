<?php

namespace PressGang\Contact;

/**
 * Trait Recaptcha
 *
 * Provides reCAPTCHA validation functionality for form submissions.
 * Utilizes Google's reCAPTCHA API to verify user responses.
 *
 * @package PressGang\Contact
 */
trait Recaptcha {

	/**
	 * Google's reCAPTCHA verification URL.
	 *
	 * @var string
	 */
	protected static $recaptcha_verify_url = 'https://www.google.com/recaptcha/api/siteverify';

	/**
	 * Minimum score threshold for reCAPTCHA v3 verification to pass.
	 *
	 * @var float
	 */
	protected static $min_score = 0.8;

	/**
	 * Verifies the reCAPTCHA response with Google's reCAPTCHA API.
	 *
	 * @return bool Returns true if the reCAPTCHA verification is successful and meets the minimum score threshold.
	 */
	public static function verify_recaptcha(): bool {
		$query    = self::build_recatpcha_query();
		$response = self::get_recaptcha_response( $query );

		return self::determine_recaptcha_success( $response );
	}

	/**
	 * Retrieves the reCAPTCHA secret key from theme settings.
	 *
	 * @return string The reCAPTCHA secret key.
	 */
	protected static function get_recaptcha_secret(): string {
		return filter_var( \get_theme_mod( 'google-recaptcha-secret', FILTER_SANITIZE_STRING ) );
	}

	/**
	 * Sends the verification request to Google's reCAPTCHA API and retrieves the response.
	 *
	 * @return object The decoded JSON response from the reCAPTCHA API.
	 */
	protected static function get_recaptcha_response(): object {
		$response = \wp_remote_post( self::$recaptcha_verify_url, [
			'body' => [
				'secret'   => self::get_recaptcha_secret(),
				'response' => $_POST['recaptcha'],
				'remoteip' => $_SERVER['REMOTE_ADDR']
			]
		] );

		if ( ! \is_wp_error( $response ) ) {
			return json_decode( \wp_remote_retrieve_body( $response ) );
		}
	}

	/**
	 * Determines if the reCAPTCHA verification is successful based on the API response.
	 *
	 * @param object $result
	 *
	 * @return bool Returns true if the reCAPTCHA verification is successful and meets the minimum score threshold.
	 */
	protected static function determine_recaptcha_success( object $result ): bool {
		return $result && $result->success && $result->score > self::$min_score;
	}

}
