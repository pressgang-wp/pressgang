<?php

namespace PressGang\Forms\Validators;

class EmailValidator implements ValidatorInterface {
	use PostDataAccessor;

	protected mixed $email_path;

	/**
	 * Construct the EmailValidator
	 *
	 * @param array|string $email_path
	 */
	public function __construct( array|string $email_path = [ 'contact', 'email' ] ) {
		$this->email_path = $email_path;
	}

	/**
	 * Validate the POSTED email
	 *
	 * @return array
	 */
	#[\Override]
	public function validate(): array {
		$errors = [];
		$email  = $this->get_post_data( $this->email_path );

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$errors[] = \__( "Invalid email address provided.", THEMENAME );
		}

		return $errors;
	}
}
