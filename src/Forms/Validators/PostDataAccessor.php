<?php

namespace PressGang\Forms\Validators;

trait PostDataAccessor {

	/**
	 * Dynamically fetches data from the $_POST array based on a specified key path.
	 *
	 * @param array<int, string>|string $keys The key(s) specifying the path to the desired data within $_POST.
	 *
	 * @return mixed The data at the specified path within $_POST, or null if not found.
	 */
	protected function get_post_data( array|string $keys ): mixed {
		$data = $_POST;
		foreach ( (array) $keys as $key ) {
			if ( is_array( $data ) && isset( $data[ $key ] ) ) {
				$data = $data[ $key ];
			} else {
				return null; // Return null if the key is not found at any point
			}
		}

		return $data;
	}

	/**
	 * Like get_post_data(), but narrows the result to a string — the shape
	 * every validator using this trait needs before its own checks.
	 *
	 * @param array<int, string>|string $keys
	 *
	 * @return string|null The string value, or null if absent or non-string.
	 */
	protected function get_post_data_string( array|string $keys ): ?string {
		$data = $this->get_post_data( $keys );

		return is_string( $data ) ? $data : null;
	}
}
