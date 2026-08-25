<?php

namespace PressGang\Forms\Validators;

class MessageValidator implements ValidatorInterface {
	use PostDataAccessor;

	protected mixed $message_path;

	/**
	 * Construct the MessageValidator
	 *
	 * @param array<int, string>|string $message_path
	 */
	public function __construct( array|string $message_path = [ 'contact', 'message' ] ) {
		$this->message_path = $message_path;
	}

	/**
	 * Validate the message
	 *
	 * @return array<int, string>
	 */
	#[\Override]
	public function validate(): array {
		$errors  = [];
		$message = $this->get_post_data_string( $this->message_path );

		if ( $message === null || trim( $message ) === '' ) {
			$errors[] = \__( "The message field cannot be empty.", THEMENAME );
		}

		return $errors;
	}
}
