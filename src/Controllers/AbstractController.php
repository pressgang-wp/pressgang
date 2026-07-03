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
	 * Declarative context manifest: keys added to the context by calling the
	 * matching getter. `'news'` calls `get_news()`; `'news' => 'latest_news'`
	 * overrides the method name. Applied after get_context(), before the
	 * pressgang context filters.
	 *
	 * The counterpart to Traits\HandlesDynamicGetters on models: getters
	 * remain the single place data is fetched, the manifest declares which of
	 * them form the template contract.
	 *
	 * @var array<int|string, string>
	 */
	protected array $context_getters = [];

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

		$this->context = $this->apply_context_getters( $this->get_context() );

		$class = new \ReflectionClass( get_called_class() );
		$key   = u( $class->getShortName() )->snake();

		$this->template = \apply_filters( "pressgang_{$key}_template", $this->template );
		$this->context  = \apply_filters( "pressgang_{$key}_context", $this->context );

		\do_action( "pressgang_render_{$key}" );

		Timber::render( $this->template, $this->context, $this->expires );

	}

	/**
	 * Adds each declared context key by calling its getter.
	 *
	 * @param array<string, mixed> $context
	 *
	 * @return array<string, mixed>
	 */
	protected function apply_context_getters( array $context ): array {

		foreach ( $this->context_getters as $key => $getter ) {
			if ( is_int( $key ) ) {
				$key    = $getter;
				$getter = "get_{$getter}";
			}

			$context[ $key ] = $this->$getter();
		}

		return $context;
	}
}
