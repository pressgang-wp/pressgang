<?php

namespace PressGang\Forms;

use PressGang\Forms\Validators\ValidatorInterface;

/**
 * Abstract class FormSubmission
 *
 * Handles the base form submission process including validation, error handling, and redirection.
 */
abstract class FormSubmission {

	/**
	 * The action used for form submission.
	 *
	 * @var string
	 */
	protected string $action;

	/**
	 * Validators to apply to form data.
	 *
	 * @var array
	 */
	protected array $validators = [];

	/**
	 * Constructor for the FormSubmission class.
	 *
	 * @param array $args Configuration array where 'action' customizes the form action and 'validators' can assign specific validators.
	 */
	public function __construct( array $args ) {
		$this->action     = $args['action'] ?? 'default_form';
		$this->validators = $args['validators'] ?? [];
	}

	/**
	 * Registers hooks for handling form submissions, accessible to logged-in and logged-out users.
	 */
	public function register_hooks(): void {
		\add_action( sprintf( 'admin_post_%s', $this->action ), [ $this, 'handle_form_submission' ] );
		\add_action( sprintf( 'admin_post_nopriv_%s', $this->action ), [ $this, 'handle_form_submission' ] );
	}

	/**
	 * Handles the form submission, checking the nonce, running validators, and processing the form.
	 */
	public function handle_form_submission() {
		if ( ! \wp_verify_nonce( $_POST['_wpnonce'], $this->action ) ) {
			\wp_die( 'Nonce verification failed', 'Security Check', [ 'response' => 403 ] );
		}

		$errors = $this->run_validators();
		if ( ! empty( $errors ) ) {
			$this->handle_errors( $errors );

			return;
		}

		$this->process_submission();
		$this->redirect_to_referrer();
	}

	/**
	 * Runs assigned validators and collects any errors.
	 *
	 * @return array An array of errors encountered during validation.
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
	 * Redirects the user back to the referring page after successful form submission.
	 */
	protected function redirect_to_referrer(): void {
		$query_args   = [ 'submitted' => '1' ];
		$redirect_url = \add_query_arg( $query_args, \wp_get_referer() );
		\wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handles any errors from the form submission.
	 *
	 * @param array $errors Errors from the form validation.
	 */
	protected function handle_errors( array $errors ) {
		\do_action( 'form_submission_error', $errors );
		\wp_die( 'Form submission errors.', 'Form Error', [ 'response' => 400 ] );
	}

	/**
	 * Processes the form submission.
	 * Must be implemented by derived classes to handle specific form actions.
	 */
	abstract protected function process_submission();
}
