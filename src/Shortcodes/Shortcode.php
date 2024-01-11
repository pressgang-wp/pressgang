<?php

namespace PressGang\Shortcodes;

use function Symfony\Component\String\u;

/**
 * Class Clients
 *
 * @package PressGang
 */
class Shortcode {

	protected $template = null;
	protected $context = [];
	protected $defaults = [];

	/**
	 * __construct
	 *
	 */
	public function __construct( $template = null, $context = null ) {
		$class     = new \ReflectionClass( get_called_class() );
		$classname = $class->getShortName();

		$shortcode = u( $classname )->snake();

		if ( $template === null ) {
			$name     = u( $class->getShortName() )->snake( '-' );
			$template = sprintf( "%s.twig", $name );
		}

		$this->template = $template;
		$this->context  = $context;

		add_shortcode( $shortcode, [ $this, 'do_shortcode' ] );
	}

	/**
	 * get_defaults
	 *
	 * Override to fill dynamic default values
	 *
	 * @return array
	 */
	protected function get_defaults() {
		return $this->defaults;
	}

	/**
	 * get_context
	 *
	 * Override to provide custom context
	 *
	 * @param $atts
	 */
	protected function get_context( $args ) {
		return $this->context = $args;
	}

	/**
	 * do_shortcode
	 *
	 * Render the shortcode template
	 *
	 * @return string
	 */
	public function do_shortcode( $atts, $content = null ) {
		$args = \shortcode_atts( $this->get_defaults(), $atts );

		return \Timber::compile( $this->template, $this->get_context( $args ) );
	}

}
