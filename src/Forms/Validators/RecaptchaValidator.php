<?php

namespace PressGang\Forms\Validators;

use PressGang\Forms\HasRecaptcha;

/**
 *
 */
class RecaptchaValidator implements ValidatorInterface {

	use HasRecaptcha;
	use PostDataAccessor;

	protected mixed $recaptcha_path;

	/**
	 * Construct the RecaptchaValidator
	 *
	 * @param array<int, string>|string $recaptcha_path
	 */
	public function __construct( array|string $recaptcha_path = 'recaptcha' ) {
		$this->recaptcha_path = $recaptcha_path;
	}

	/**
	 * @return array<int, string>
	 */
	#[\Override]
	public function validate(): array {

		$errors    = [];
		$recaptcha = $this->get_post_data_string( $this->recaptcha_path );

		if ( $recaptcha === null || ! self::verify_recaptcha( $recaptcha ) ) {
			$errors[] = \__( "Failed reCAPTCHA verification. Please try again.", THEMENAME );
		}

		return $errors;
	}
}
