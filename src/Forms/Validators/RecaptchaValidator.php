<?php

namespace PressGang\Forms\Validators;

use PressGang\Forms\HasRecaptcha;

/**
 *
 */
class RecaptchaValidator implements ValidatorInterface {
	use HasRecaptcha;

	protected mixed $recaptcha_path;

	/**
	 * Construct the RecaptchaValidator
	 *
	 * @param array|string $recaptcha_path
	 */
	public function __construct( array|string $recaptcha_path = 'recaptcha' ) {
		$this->recaptcha_path = $recaptcha_path;
	}

	/**
	 * @return array
	 */
	public function validate(): array {

		$errors    = [];
		$recaptcha = $this->get_post_data( $this->recaptcha_path );

		if ( ! self::verify_recaptcha( $recaptcha ) ) {
			$errors[] = \__( "Failed reCAPTCHA verification. Please try again.", THEMENAME );
		}

		return $errors;
	}
}

