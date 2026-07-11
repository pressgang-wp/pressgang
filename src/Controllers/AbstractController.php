<?php

namespace PressGang\Controllers;

use Timber\Loader;
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

	/**
	 * Twig template to render: a single name, or a fallback chain rendered
	 * first-existing-wins (Timber accepts an array, mirroring the WP hierarchy).
	 *
	 * @var string|array<int, string>|null
	 */
	public string|array|null $template;

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
	 * @param string|array<int, string>|null $template
	 */
	public function __construct( string|array|null $template = null ) {
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

		// Timber::render() is void and echoes compile()'s output — echoing the
		// `false` it produces for a missing template prints nothing, which is
		// exactly the silent-blank-page bug. Compile explicitly (via_render
		// keeps the timber/render/* filter semantics identical) so failure is
		// observable.
		$output = Timber::compile( $this->template, $this->context, $this->expires, Loader::CACHE_USE_DEFAULT, true );

		if ( false === $output ) {
			$this->handle_render_failure();

			return;
		}

		echo $output;
	}

	/**
	 * Surfaces a failed render instead of letting WordPress serve a blank 200.
	 *
	 * Timber::render() returns false when none of the requested templates
	 * exist — historically an easy way to ship a silently empty page. Logs the
	 * failure, fires `pressgang_render_failed` for observers, and raises a
	 * warning under WP_DEBUG.
	 *
	 * @return void
	 */
	protected function handle_render_failure(): void {

		$message = sprintf(
			'PressGang: %s rendered no output — no Twig template found (tried: %s).',
			static::class,
			implode( ', ', (array) $this->template ) ?: '(none)'
		);

		\do_action( 'pressgang_render_failed', static::class, $this->template );

		error_log( $message );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			trigger_error( esc_html( $message ), E_USER_WARNING );
		}
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
