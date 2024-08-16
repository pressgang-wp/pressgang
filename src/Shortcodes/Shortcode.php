<?php

namespace PressGang\Shortcodes;

use Timber\Timber;

/**
 * Abstract Class Shortcode
 *
 * Base class for creating shortcodes in PressGang.
 *
 * @package PressGang
 */
abstract class Shortcode {

	protected ?string $template;
	protected array $context = [];
	protected array $defaults = [];
	protected string $view_folder = 'shortcodes';

	/**
	 * Constructor to initialize the shortcode.
	 *
	 * @param string|null $template Path to the template file.
	 * @param array|null $context Context data for rendering the template.
	 */
	public function __construct( ?string $template = null, ?array $context = null ) {

		$class     = new \ReflectionClass( get_called_class() );
		$classname = $class->getShortName();

		// Dynamically generate the shortcode name
		$shortcode = $this->generate_shortcode_name( $classname );

		if ( $template === null ) {
			$template = $this->generate_template_name( $classname );
		}

		$this->template = $template;
		$this->context  = $context ?? [];

		\add_shortcode( $shortcode, [ $this, 'do_shortcode' ] );
	}

	/**
	 * Generate a shortcode name based on the class name.
	 *
	 * @param string $classname The class name.
	 *
	 * @return string The generated shortcode name.
	 */
	protected function generate_shortcode_name( string $classname ): string {
		return strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $classname ) );
	}

	/**
	 * Generate a template name based on the class name.
	 *
	 * @param string $classname The class name.
	 *
	 * @return string The generated template name.
	 */
	protected function generate_template_name( string $classname ): string {
		$name = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $classname ) );

		return sprintf( "%s/%s.twig", $this->view_folder, $name );
	}

	/**
	 * Get the default attributes for the shortcode.
	 *
	 * Override to provide dynamic default values.
	 *
	 * @return array
	 */
	protected function get_defaults(): array {
		return array_merge( $this->defaults, $this->define_defaults() );
	}

	/**
	 * Get the context data for rendering the template.
	 *
	 * Override to provide custom context.
	 *
	 * @param array $args Shortcode attributes.
	 *
	 * @return array Modified context array.
	 */
	protected function get_context( array $args ): array {
		$context = array_merge( $this->context, $args );

		return $this->define_context( $context );
	}

	/**
	 * Render the shortcode template.
	 *
	 * @param array $atts Shortcode attributes.
	 * @param string|null $content The enclosed content (if any).
	 *
	 * @return string The rendered template output.
	 */
	public function do_shortcode( array $atts, ?string $content = null ): string {
		$args = \shortcode_atts( $this->get_defaults(), $atts );

		return Timber::compile( $this->template, $this->get_context( $args ) );
	}

	/**
	 * Abstract method to define the shortcode's default attributes.
	 *
	 * @return array
	 */
	abstract protected function define_defaults(): array;

	/**
	 * Abstract method to define the context data for the shortcode template.
	 *
	 * @param array $args Shortcode attributes.
	 *
	 * @return array
	 */
	abstract protected function define_context( array $args ): array;
}
