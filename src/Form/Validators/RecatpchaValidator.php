<?php

namespace PressGang\Form\Validators;

use PressGang\Form\Recaptcha;

class RecaptchaValidator implements ValidatorInterface {
	use Recaptcha;

	public function validate(): array {
		if ( ! self::verify_recaptcha( $_POST['recaptcha'] ) ) {
			return [ \__( "Failed reCAPTCHA verification. Please try again.", THEMENAME ) ];
		}

		return [];
	}
}

