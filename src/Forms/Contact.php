<?php

namespace PressGang\Forms;

use PressGang\Util\Flash;
use Timber\Timber;

/**
 * Class Contact
 *
 * Extends FormSubmission to handle specific functionality for contact form submissions, including email preparation and sending.
 */
class Contact extends FormSubmission {

	/**
	 * Optional Twig template for formatting the email.
	 *
	 * @var string|null
	 */
	protected ?string $template = null;

	/**
	 * Message displayed on successful form submission.
	 *
	 * @var string
	 */
	protected string $success_message;

	/**
	 * Message displayed on form submission error.
	 *
	 * @var string
	 */
	protected string $error_message;

	/**
	 * Contact constructor.
	 *
	 * Initializes a new instance of the Contact form handling class.
	 *
	 * @param array $args Associative array of initialization options.
	 */
	public function __construct( array $args ) {
		parent::__construct( $args );
		$this->template        = $args['template'] ?? null;
		$this->success_message = $args['success_message'] ?? __( "Thanks for your message. We'll be in touch shortly.", THEMENAME );
		$this->error_message   = $args['error_message'] ?? __( "Please correct the errors and try again.", THEMENAME );
	}

	/**
	 * Initializes and registers the class instance into the WordPress lifecycle.
	 * Should be called from the WordPress theme or plugin setup file.
	 *
	 * @param array $args Configuration parameters for setting up the class.
	 */
	public static function init( array $args ): void {
		$instance = new self( $args );
		\add_action( 'init', [ $instance, 'register_hooks' ] );
	}

	/**
	 * Processes the form submission specific to contact forms.
	 * Handles the preparation and sending of the contact email.
	 */
	protected function process_submission(): void {
		$to      = \apply_filters( 'pressgang_contact_to_email', \sanitize_email( \get_option( 'admin_email' ) ) );
		$subject = \apply_filters( 'pressgang_contact_subject', \__( "New Contact Message", THEMENAME ) );
		$email   = \sanitize_email( $_POST['contact']['email'] ?? '' );
		$message = \sanitize_text_field( $_POST['contact']['message'] ?? '' );

		$prepared_message = $this->prepare_message( $email, $message );
		$success          = $this->send_email( $to, $subject, $prepared_message );

		if ( $success ) {
			\do_action( 'pressgang_contact_after_successful_submission', $email );
			Flash::add( 'contact_form_success', $this->success_message );
		} else {
			\do_action( 'pressgang_contact_submission_error', 'email_send_failure' );
			Flash::add( 'contact_form_errors', $this->error_message );
		}
	}

	/**
	 * Prepares the contact email message.
	 *
	 * @param string $email The sender's email address.
	 * @param string $message The message content from the form.
	 *
	 * @return string The formatted email message.
	 */
	protected function prepare_message( string $email, string $message ): string {
		if ( $this->template ) {
			return Timber::compile( $this->template, [ 'email' => $email, 'message' => $message ] );
		} else {
			return "From: $email\r\nMessage: $message\r\n";
		}
	}

	/**
	 * Sends the email.
	 *
	 * @param string $to The recipient's email address.
	 * @param string $subject The subject of the email.
	 * @param string $message The body of the email.
	 *
	 * @return bool True if the email was successfully sent, false otherwise.
	 */
	protected function send_email( string $to, string $subject, string $message ): bool {
		return \wp_mail( $to, $subject, $message );
	}
}
