<?php

namespace PressGang\Tests\Unit;

use PressGang\Configuration\ConfigurationSingleton;
use Yoast\WPTestUtils\BrainMonkey\YoastTestCase;

/**
 * Base test case for PressGang unit tests.
 *
 * Extends YoastTestCase which sets up BrainMonkey and pre-stubs common
 * WordPress functions (wp_parse_args, esc_html__, etc.).
 */
abstract class TestCase extends YoastTestCase {

	/**
	 * Clears all ConfigurationSingleton instances, preventing state leakage between tests.
	 */
	protected function resetSingletonInstances(): void {
		ConfigurationSingleton::reset_instances();
	}

	/**
	 * Populates $_POST for tests that exercise the PostDataAccessor trait.
	 *
	 * @param array<string, mixed> $data
	 */
	protected function setPostData( array $data ): void {
		$_POST = $data;
	}

	/**
	 * Resets $_POST to an empty array.
	 */
	protected function clearPostData(): void {
		$_POST = [];
	}
}
