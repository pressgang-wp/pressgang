<?php

namespace PressGang\Forms\Validators;

class MessageValidator implements ValidatorInterface {
	use PostDataAccessor;

	protected mixed $message_path;

	/**
	 * Construct the MessageValidator
	 *
	 * @param array|string $message_path
	 */
	public function __construct( array|string $message_path = [ 'contact', 'message' ] ) {
		$this->message_path = $message_path;
	}

	/**
	 * Validate the message
	 *
	 * @return array
	 */
	public function validate(): array {
		$errors  = [];
		$message = $this->get_post_data( $this->message_path );

		if ( empty( trim( $message ) ) ) {
			$errors[] = \__( "The message field cannot be empty.", THEMENAME );
		}

		return $errors;
	}
}
