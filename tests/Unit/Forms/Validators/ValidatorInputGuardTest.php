<?php

namespace PressGang\Tests\Unit\Forms\Validators;

use Brain\Monkey\Functions;
use PressGang\Forms\Validators\EmailValidator;
use PressGang\Forms\Validators\MessageValidator;
use PressGang\Forms\Validators\RecaptchaValidator;
use PressGang\Tests\Unit\TestCase;

class ValidatorInputGuardTest extends TestCase {

	#[\Override]
	protected function set_up(): void {
		parent::set_up();

		Functions\when( '__' )->returnArg( 1 );
	}

	#[\Override]
	protected function tear_down(): void {
		$this->clearPostData();

		parent::tear_down();
	}

	/** @test */
	public function email_validator_rejects_missing_or_non_string_values(): void {
		$this->setPostData( [ 'contact' => [ 'email' => [ 'not' => 'an email' ] ] ] );

		$errors = ( new EmailValidator() )->validate();

		$this->assertSame( [ 'Invalid email address provided.' ], $errors );
	}

	/** @test */
	public function message_validator_rejects_missing_or_non_string_values(): void {
		$this->setPostData( [ 'contact' => [ 'message' => [ 'not' => 'a message' ] ] ] );

		$errors = ( new MessageValidator() )->validate();

		$this->assertSame( [ 'The message field cannot be empty.' ], $errors );
	}

	/** @test */
	public function recaptcha_validator_rejects_non_string_values_before_remote_verification(): void {
		$this->setPostData( [ 'recaptcha' => [ 'not' => 'a token' ] ] );

		$errors = ( new RecaptchaValidator() )->validate();

		$this->assertSame( [ 'Failed reCAPTCHA verification. Please try again.' ], $errors );
	}
}
