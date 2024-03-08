<?php

namespace PressGang\Form;

use PressGang\Form\Validators\ValidatorInterface;
use PressGang\Util\Flash;

/**
 * Class Contact
 *
 * Handles contact form submissions, including validation, sanitization, and response management.
 *
 * @package PressGang\Form
 */
class Contact {

	/**
	 * Action name for form submission
	 *
	 * @var string|mixed
	 */
	protected string $action;

	/**
	 * Optional twig template for the email
	 *
	 * @var string|mixed|null
	 */
	protected ?string $template = null;

	/**
	 * Success message
	 *
	 * @var string|mixed
	 */
	protected string $successMessage;

	/**
	 * Error message
	 *
	 * @var string|mixed
	 */
	protected string $errorMessage;

	/**
	 * Array to hold validators
	 *
	 * @var array
	 */
	protected array $validators = [];

	/**
	 * @param array $args
	 */
	public function __construct( array $args ) {
		$this->action         = $args['action'] ?? 'contact_form';
		$this->template       = $args['template'] ?? null;
		$this->successMessage = $args['successMessage'] ?? __( "Thanks for your message. We'll be in touch shortly.", THEMENAME );
		$this->errorMessage   = $args['errorMessage'] ?? __( "Please correct the errors and try again.", THEMENAME );
		$this->validators     = $args['validators'] ?? [];

		\add_action( 'init', [ $this, 'register_hooks' ] );
		\add_action( 'pressgang_contact_after_successful_submission', [ $this, 'set_success_flash_message' ], 10, 2 );
		\add_action( 'pressgang_contact_submission_error', [ $this, 'set_error_flash_message' ], 10, 2 );
	}

	/**
	 * Registers WordPress hooks for handling form submissions.
	 */
	public function register_hooks(): void {
		\add_action( sprintf( 'admin_post_%s', $this->action ), [ $this, 'handle_form_submission' ] );
		\add_action( sprintf( 'admin_post_nopriv_%s', $this->action ), [ $this, 'handle_form_submission' ] );
	}

	/**
	 * Handles the form submission, including validation, sending emails, and redirecting.
	 */
	public function handle_form_submission() {
		if ( ! \wp_verify_nonce( $_POST['_wpnonce'], $this->action ) ) {
			\wp_die( 'Nonce verification failed', 'Security Check', [ 'response' => 403 ] );
		}

		\do_action( 'pressgang_contact_before_submission' );

		$to      = \apply_filters( 'pressgang_contact_to_email', \sanitize_email( \get_option( 'admin_email' ) ) );
		$subject = \apply_filters( 'pressgang_contact_subject', __( "New Contact Message", THEMENAME ) );

		$email   = '';
		$message = '';

		// Any extra form parameters
		$params = [];

		if ( ! isset( $_POST['contact'] ) || ! is_array( $_POST['contact'] ) ) {
			foreach ( $_POST['contact'] as $key => $val ) {
				switch ( $key ) {
					case 'email':
						$email = filter_var( $val, FILTER_SANITIZE_EMAIL );
						break;
					default:
						$params[ $key ] = \sanitize_text_field( $val );
						break;
				}
			}
		} else {
			// Handle the error if the expected data isn't present
			\do_action( 'pressgang_contact_submission_error', 'invalid_post_data' );
			\wp_die( 'Invalid form submission.', 'Form Error', [ 'response' => 400 ] );

			return false;
		}

		// Initialize an array to hold potential validation errors
		$errors = $this->run_validators();

		\do_action_ref_array( 'pressgang_contact_form_custom_validation', [ &$errors ] );

		if ( empty( $errors ) ) {
			$prepared_message = $this->prepare_message( $email, $message, $params );
			$success          = $this->send_email( $to, $subject, $prepared_message, $email );
			if ( $success ) {
				\do_action( 'pressgang_contact_after_successful_submission', $email, $params );
			} else {
				\do_action( 'pressgang_contact_submission_error', 'email_send_failure', $params );
			}
		} else {
			\do_action( 'pressgang_contact_submission_error', 'validation_error', $errors );
		}

		$this->redirect_to_referrer();
	}

	/**
	 * @return array
	 */
	protected function run_validators(): array {
		$errors = [];
		foreach ( $this->validators as $validator ) {
			if ( $validator instanceof ValidatorInterface ) {
				$errors = array_merge( $errors, $validator->validate() );
			}
		}

		return $errors;
	}

	/**
	 * @return void
	 */
	protected function redirect_to_referrer(): void {
		$query_args   = [ 'submitted' => '1' ]; // Flag indicating form submission
		$redirect_url = \add_query_arg( $query_args, \wp_get_referer() );
		\wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Prepares the email message to be sent.
	 *
	 * @param string $email
	 * @param string $message
	 * @param array $params
	 *
	 * @return string
	 */
	protected function prepare_message( string $email, string $message, array $params = [] ): string {

		if ( $this->template ) {
			$params['email']   = $email;
			$params['message'] = $message;

			return Timber::compile( $this->template, $params );
		} else {
			$formatted_message = "From: $email\r\n";
			foreach ( $params as $key => $val ) {
				if ( ! in_array( $key, [ 'recaptcha', 'to', 'success', 'email' ] ) ) { // Avoid duplicating certain keys
					$formatted_message .= sprintf( "%s: %s\r\n", ucwords( $key ), $val );
				}
			}
			$formatted_message .= "\r\nMessage: $message\r\n";

			return $formatted_message;
		}

	}

	/**
	 * Sends the email message.
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @param string|null $from_email
	 * @param string|null $from_name
	 *
	 * @return bool
	 */
	protected function send_email( string $to, string $subject, string $message, string $from_email = null, string $from_name = null ): bool {
		$headers = $from_name && $from_email ? [ 'Reply-To: ' . $from_name . ' <' . $from_email . '>' ] : [];

		return \wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * @param $email
	 * @param $params
	 *
	 * @return void
	 */
	public function set_success_flash_message( $email, $params ): void {
		Flash::add( 'contact_form_success', $this->successMessage );
	}

	/**
	 * @param $error_type
	 * @param $params
	 *
	 * @return void
	 */
	public function set_error_flash_message( $error_type, $params ): void {
		Flash::add( 'contact_form_errors', $this->errorMessage );
	}

}
