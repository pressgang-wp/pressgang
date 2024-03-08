<?php

namespace PressGang\Form;

/**
 * Trait HasGoogleAnalyticsEvent
 *
 * Provides functionality for registering and sending a Google Analytics event upon form submission.
 * This trait can be used in any class to add Google Analytics event tracking to form submissions,
 * assuming a query parameter 'submitted' is used to indicate a successful submission.
 */
trait HasGoogleAnalyticsEvent {

	protected static string $ga_category = 'Form Submission';
	protected static string $ga_action = 'submit';
	protected static string $ga_label = '';

	/**
	 * Initializes Google Analytics event tracking with custom parameters.
	 *
	 * @param string $form_action The unique action name associated with the form.
	 * @param string $ga_category The event category (e.g., 'Contact Form', 'Newsletter Signup').
	 * @param string $ga_action The event action (e.g., 'submit').
	 * @param string $ga_label Optional. The event label for additional categorization.
	 */
	public static function maybe_init_ga_event_tracking( string $form_action, string $ga_category, string $ga_action, string $ga_label = '' ): void {
		if ( isset( $_POST['action'] ) && $_POST['action'] === $form_action ) {
			self::$ga_category = $ga_category;
			self::$ga_action   = $ga_action;
			self::$ga_label    = $ga_label;

			if ( isset( $_GET['submitted'] ) && $_GET['submitted'] === '1' ) {
				self::register_ga_event_handler();
			}
		}
	}

	/**
	 * Registers the Google Analytics event tracking handler for form submissions.
	 */
	protected static function register_ga_event_handler(): void {
		\add_action( 'wp_footer', [ get_called_class(), 'send_ga_event' ] );
	}

	/**
	 * Outputs the Google Analytics tracking script with configured parameters.
	 *
	 */
	public static function send_ga_event(): void {
		$ga_label = self::$ga_label ? ", '" . \esc_js( self::$ga_label ) . "'" : '';
		echo "<script>ga('send', 'event', '" . \esc_js( self::$ga_category ) . "', '" . \esc_js( self::$ga_action ) . "'" . $ga_label . ");</script>";
	}
}
