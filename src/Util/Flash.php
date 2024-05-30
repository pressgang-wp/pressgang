<?php

namespace PressGang\Util;

/**
 * Class Flash
 *
 * Manages flash messages for single-session user notifications. Flash messages are stored in the session and are
 * meant to be displayed once, typically after form submissions or actions where feedback is required. The class
 * provides methods to add, get, delete, and clear messages ensuring they are handled securely and efficiently.
 *
 * Flash messages are useful for providing feedback to the user, such as success or error messages, without the need
 * for persistent storage or passing messages through URL parameters.
 *
 * Usage:
 * - Flash::add('key', 'Message'); To add a flash message.
 * - $message = Flash::get('key'); To retrieve and automatically clear a message.
 * - Flash::delete('key'); To manually delete a specific message.
 * - Flash::clear(); To clear all flash messages.
 *
 * @package PressGang\Classes
 */
class Flash {

	/**
	 * Starts the session if it hasn't already been started, ensuring flash messages can be stored.
	 * Initialize the session safely without conflicting with WordPress or other plugins.
	 */
	protected static function session(): void {
		if ( ! session_id() ) {
			session_start( [
				'read_and_close' => false, // Keep session writable during the script's execution
			] );
		}
	}

	/**
	 * Retrieves a flash message by key and optionally clears it from the session.
	 *
	 * @param string $key The key for the flash message.
	 * @param mixed|null $default The default value to return if the key does not exist.
	 *
	 * @return mixed The flash message if it exists, or $default if not.
	 */
	public static function get( string $key, mixed $default = null ): mixed {
		self::session();
		$value = $_SESSION['flash'][ $key ] ?? $default;
		self::schedule_flash_clear( $key );

		return $value;
	}

	/**
	 * Adds a flash message under a specific key.
	 *
	 * @param string $key The key for the flash message.
	 * @param mixed $value The value of the flash message.
	 */
	public static function add( string $key, mixed $value ): void {
		self::session();
		$_SESSION['flash'][ $key ] = $value;
	}

	/**
	 * Schedules the clearing of a specific flash message.
	 *
	 * @param string $key The key of the flash message to clear.
	 */
	protected static function schedule_flash_clear( string $key ): void {
		\add_action( 'shutdown', function () use ( $key ) {
			self::delete( $key );
		} );
	}

	/**
	 * Deletes a specific flash message from the session.
	 *
	 * @param string $key The key of the flash message to delete.
	 */
	public static function delete( string $key ): void {
		self::session();
		if ( isset( $_SESSION['flash'][ $key ] ) ) {
			unset( $_SESSION['flash'][ $key ] );
		}
	}

	/**
	 * Clears all flash messages from the session.
	 */
	public static function clear(): void {
		self::session();
		if ( isset( $_SESSION['flash'] ) ) {
			unset( $_SESSION['flash'] );
		}
	}
}
