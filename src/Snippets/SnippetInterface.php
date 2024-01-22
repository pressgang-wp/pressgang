<?php

namespace PressGang\Snippets;

/**
 * Interface SnippetInterface
 *
 * Defines the standard structure for snippet classes within a PressGang theme. Each snippet
 * is responsible for a specific functionality and is initialized through its constructor.
 *
 * This interface ensures that each snippet class adheres to a consistent initialization pattern.
 */
interface SnippetInterface {

	/**
	 * Constructor for the snippet.
	 *
	 * Responsible for initializing the snippet, including setting up necessary WordPress hooks,
	 * e.g. for enqueuing scripts and styles, and any other configuration.
	 *
	 * The constructor is invoked for each snippet during the theme setup, allowing each snippet to
	 * be configured with specific arguments.
	 *
	 * @param array $args Associative array of arguments for the snippet initialization. The specific
	 *                    structure of this array varies based on the snippet's requirements.
	 */
	public function __construct( array $args );
}
