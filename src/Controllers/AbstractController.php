<?php

namespace PressGang\Controllers;

use Timber\Timber;
use function Symfony\Component\String\u;

/**
 * Abstract base class for controllers in the PressGang theme.
 *
 * Provides common functionalities for controllers including context management
 * and rendering of Twig templates using the Timber library.
 */
abstract class AbstractController implements ControllerInterface {

	/**
	 * The Timber context array.
	 *
	 * Contains data that is passed to the Twig templates for rendering.
	 *
	 * @var array
	 */
	public array $context;

	/**
	 * The path or name of the Twig template to be rendered.
	 *
	 * @var string|null
	 */
	public ?string $template;

	/**
	 * Cache expiration time for the rendered template.
	 *
	 * @var bool|int
	 */
	protected int|bool $expires = false;

	/**
	 * Constructor for the AbstractController class.
	 *
	 * Initializes the Timber context and sets the template path.
	 *
	 * @param string|null $template Optional. The path or name of the Twig template.
	 */
	public function __construct( string|null $template = null ) {
		$this->template = $template;
		$this->context  = Timber::context();
	}

	/**
	 * Retrieves the current Timber context.
	 *
	 * This method can be overridden in child classes to modify the context data.
	 *
	 * @return array The Timber context array.
	 */
	protected function get_context(): array {
		return $this->context;
	}

	/**
	 * Renders the Twig template with the current context.
	 *
	 * Applies filters to the template path and context, executes an action before rendering,
	 * and then renders the template using Timber.
	 */
	public function render(): void {

		$this->context = $this->get_context();

		$class = new \ReflectionClass( get_called_class() );
		$key   = u( $class->getShortName() )->snake();

		$this->template = \apply_filters( "pressgang_{$key}_template", $this->template );
		$this->context  = \apply_filters( "pressgang_{$key}_context", $this->context );

		\do_action( "pressgang_render_{$key}" );

		Timber::render( $this->template, $this->context, $this->expires );

	}
}
