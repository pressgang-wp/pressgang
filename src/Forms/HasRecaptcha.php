<?php

namespace PressGang\Forms;

/**
 * Trait Recaptcha
 *
 * Provides reCAPTCHA validation functionality for form submissions.
 * Utilizes Google's reCAPTCHA API to verify user responses.
 *
 * @package PressGang\Form
 */
trait HasRecaptcha {

	/**
	 * Google's reCAPTCHA verification URL.
	 *
	 * @var string
	 */
	protected static string $recaptcha_verify_url = 'https://www.google.com/recaptcha/api/siteverify';

	/**
	 * Minimum score threshold for reCAPTCHA v3 verification to pass.
	 *
	 * @var float
	 */
	protected static float $min_score = 0.8;

	/**
	 * Verifies the reCAPTCHA response with Google's reCAPTCHA API.
	 *
	 * @param $value - the reCAPTCHA form value to validate.
	 *
	 * @return bool Returns true if the reCAPTCHA verification is successful and meets the minimum score threshold.
	 */
	public static function verify_recaptcha( string $value ): bool {
		$secret   = self::get_recaptcha_secret();
		$response = self::get_recaptcha_response( $secret, $value );

		return self::determine_recaptcha_success( $response );
	}

	/**
	 * Retrieves the sanitized reCAPTCHA secret from theme settings.
	 *
	 * Designed to work with PressGang\Snippets\GoogleRecaptcha for storing the key.
	 * Override as needed.
	 *
	 * @return string The sanitized reCAPTCHA secret
	 */
	protected static function get_recaptcha_secret(): string {
		return \sanitize_text_field( \get_theme_mod( 'google-recaptcha-secret' ) );
	}

	/**
	 * Retrieves the sanitized reCAPTCHA site key from theme settings.
	 *
	 * Designed to work with PressGang\Snippets\GoogleRecaptcha for storing the key.
	 * Override as needed.
	 *
	 * @return string The sanitized reCAPTCHA site key
	 */
	protected static function get_recaptcha_site_key(): string {
		return \sanitize_text_field( \get_theme_mod( 'google-recaptcha-site-key' ) );
	}

	/**
	 * Sends the verification request to Google's reCAPTCHA API and retrieves the response.
	 *
	 * @param $secret - the reCAPTCHA API Secret
	 * @param $value - the reCAPTCHA Form Value
	 *
	 * @return object|null The decoded JSON response from the reCAPTCHA API, or null on failure.
	 */
	protected static function get_recaptcha_response( string $secret, string $value ): ?object {
		$response = \wp_remote_post( self::$recaptcha_verify_url, [
			'body' => [
				'secret'   => $secret,
				'response' => $value,
				'remoteip' => $_SERVER['REMOTE_ADDR']
			]
		] );

		if ( ! \is_wp_error( $response ) ) {
			return json_decode( \wp_remote_retrieve_body( $response ) );
		}

		return null;
	}

	/**
	 * Determines if the reCAPTCHA verification is successful based on the API response.
	 *
	 * @param object|null $result
	 *
	 * @return bool Returns true if the reCAPTCHA verification is successful and meets the minimum score threshold.
	 */
	protected static function determine_recaptcha_success( ?object $result ): bool {
		return $result && $result->success && $result->score > self::$min_score;
	}

}
