<?php

namespace PressGang\Controllers;

use Timber\Timber;
use function Symfony\Component\String\u;

/**
 * Base class for all PressGang controllers. Initialises the Timber context,
 * provides the render() workflow (context → filters → actions → Timber::render),
 * and applies pressgang_{controller}_template / pressgang_{controller}_context filters.
 */
abstract class AbstractController implements ControllerInterface {

	/** @var array<string, mixed> */
	public array $context;

	/** @var string|null */
	public ?string $template;

	/** @var int|bool */
	protected int|bool $expires = false;

	/**
	 * Initialises the Timber context and sets the template if provided.
	 * Subclasses can override the template by passing it to the parent constructor.
	 * Subclasses can also override the context by overriding the get_context() method.
	 *
	 * @param string|null $template
	 */
	public function __construct( ?string $template = null ) {
		$this->template = $template;
		$this->context  = Timber::context();
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_context(): array {
		return $this->context;
	}

	/**
	 * Renders the template with context, applying pressgang filters and actions.
	 *
	 * @return void
	 */
	#[\Override]
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
