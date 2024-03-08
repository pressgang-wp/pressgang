<?php

namespace PressGang\Form;

trait GoogleAnalyticsEvent {

	/**
	 * Registers the Google Analytics event tracking handler for form submissions.
	 */
	protected static function register_ga_event_handler(): void {
		if ( isset( $_GET['submitted'] ) && $_GET['submitted'] === '1' ) {
			\add_action( 'wp_footer', [ get_called_class(), 'send_ga_event' ] );
		}
	}

	/**
	 * Outputs the Google Analytics tracking script.
	 */
	public static function send_ga_event(): void {
		echo "<script>ga('send', 'event', 'Contact Form', 'submit');</script>";
	}
}
