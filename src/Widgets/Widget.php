<?php

namespace PressGang\Widgets;

use Timber\Timber;
use ReflectionClass;
use WP_Widget;

/**
 * Base class for PressGang widgets. Derives the widget ID, title, and Twig template
 * from the class name, and merges ACF fields into the instance data automatically.
 * Subclasses define fields/defaults and are registered via config/widgets.php.
 */
abstract class Widget extends WP_Widget {

	protected string $classname;
	protected string $description;
	protected string $view;
	protected string $title;
	protected array $fields = [];
	protected array $defaults = [];

	/**
	 * Constructor to initialize the widget.
	 */
	public function __construct() {
		$class     = new ReflectionClass( get_called_class() );
		$classname = $this->convert_to_snake_case( $class->getShortName() );

		$this->classname   = $this->classname ?? sprintf( "widget-%s", $classname );
		$this->view        = $this->view ?? sprintf( "%s.twig", $classname );
		$this->title       = $this->title ?? __( ucwords( str_replace( '_', ' ', $classname ) ), THEMENAME );
		$this->description = $this->description ?? sprintf( "%s Widget", $this->title );

		$widget_ops = [
			'classname'   => $this->classname,
			'description' => $this->description,
		];

		parent::__construct( $classname, $this->title, $widget_ops );
	}

	/**
	 * Convert a string to snake_case.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected function convert_to_snake_case( string $string ): string {
		return strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $string ) );
	}

	/**
	 * Display the widget content on the frontend.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$instance = $this->get_instance( $args, $instance );

		$class     = new ReflectionClass( get_called_class() );
		$classname = $class->getShortName();
		$name      = $this->convert_to_snake_case( $classname );

		do_action( "render_widget_{$name}" );

		Timber::render( $this->view, $instance );
	}

	/**
	 * Get the instance data for the widget.
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return array
	 */
	protected function get_instance( $args, $instance ): array {
		$instance = array_merge( $instance, [
			'before_widget' => $args['before_widget'] ?? '',
			'after_widget'  => $args['after_widget'] ?? '',
			'before_title'  => $args['before_title'] ?? '',
			'after_title'   => $args['after_title'] ?? '',
		], $this->get_acf_fields( $args['widget_id'] ) );

		return $instance;
	}

	/**
	 * Update the widget options.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ): array {
		$instance = $old_instance;

		foreach ( $this->fields as $field => &$config ) {
			$instance[ $field ] = \sanitize_text_field( $new_instance[ $field ] ?? '' );
		}

		return $instance;
	}

	/**
	 * Display the widget form in the admin area.
	 *
	 * @param array $instance
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		foreach ( $this->fields as $field => &$config ) {
			Timber::render( $config['view'], [
				'label' => __( $config['label'], THEMENAME ),
				'id'    => $this->get_field_id( $field ),
				'name'  => $this->get_field_name( $field ),
				'value' => isset( $instance[ $field ] ) ? esc_attr( $instance[ $field ] ) : '',
				'class' => $config['class'],
			] );
		}
	}

	/**
	 * Get ACF fields for the widget.
	 *
	 * @param string $widget_id
	 *
	 * @return array
	 */
	protected function get_acf_fields( $widget_id ): array {
		if ( function_exists( 'get_fields' ) ) {
			$fields = get_fields( "widget_{$widget_id}" );
			if ( $fields ) {
				return $fields;
			}
		}

		return [];
	}

	/**
	 * Define the default attributes for this widget.
	 *
	 * @return array
	 */
	abstract protected function define_defaults(): array;

	/**
	 * Define the fields for the widget form.
	 *
	 * @return array
	 */
	abstract protected function define_fields(): array;

	/**
	 * Register the widget with WordPress.
	 *
	 * This static method can be used by child classes to register themselves.
	 *
	 * @param string $widget_class The class name of the widget to register.
	 */
	public static function register( string $widget_class ): void {
		add_action( 'widgets_init', function () use ( $widget_class ) {
			register_widget( $widget_class );
		} );
	}
}
