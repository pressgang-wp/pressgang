<?php

/**
 * Adds a flash message.
 *
 * This function provides a convenient way to add flash messages
 * by abstracting the direct use of the Flash class.
 *
 * @param string $key The key under which to store the message.
 * @param string $message The message content.
 */
function add_flash_message( $key, $message ) {
	PressGang\Classes\Flash::add( $key, $message );
}

/**
 * Retrieves and optionally clears a flash message.
 *
 * Simplifies the process of getting flash messages, with an
 * option to leave the message in the session if not to be cleared immediately.
 *
 * @param string $key The key of the message to retrieve.
 * @param bool $clear Whether to clear the message after retrieval (default true).
 *
 * @return mixed The message if found; otherwise, null.
 */
function get_flash_message( $key, $clear = true ) {
	return PressGang\Classes\Flash::get( $key, null, $clear );
}

/**
 * Deletes a specific flash message.
 *
 * Allows for explicit removal of a flash message from the session,
 * without needing to retrieve it first.
 *
 * @param string $key The key of the message to delete.
 */
function delete_flash_message( $key ) {
	PressGang\Classes\Flash::delete( $key );
}

/**
 * Clears all stored flash messages.
 *
 * Use this function to remove all messages from the session at once,
 * typically done at the start or end of a request lifecycle.
 */
function clear_flash_messages() {
	PressGang\Classes\Flash::clear();
}
