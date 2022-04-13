<?php

namespace PressGang;

use Timber\Timber;

class Block {

	protected static $id = null;
	protected static $context = array();

	/**
	 * render
	 *
	 * @param $block
	 */
	public static function render( $block ) {
		// convert name into path friendly slug
		$slug       = substr( $block['name'], strpos( $block['name'], '/' ) + 1, strlen( $block['name'] ) );
		static::$id = $block['id'];

		$context            = static::get_context( $block );
		$context['classes'] = static::get_css_classes( $block );

		Timber::render( "blocks/{$slug}.twig", $context );
	}

	/**
	 * Determine the Gutenberg classes applied to the Block, when the block 'supports' color or className
	 *
	 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/#color
	 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/#classname
	 *
	 * @param $block
	 *
	 * @return array
	 */
	private static function get_css_classes( $block ) {

		$classes = [];

		if ( isset( $block['className'] ) ) {
			$classes[] = $block['className'];
		}

		if ( isset( $block['backgroundColor'] ) ) {
			$classes[] = $block['backgroundColor'];
			$classes[] = sprintf( "has-%s-background-color", $block['backgroundColor'] );
		}

		if ( isset( $block['textColor'] ) ) {
			$classes[] = sprintf( "has-%s-color", $block['textColor'] );
		}

		return $classes;
	}

	/**
	 * get_context
	 *
	 * @param $block
	 *
	 * @return array
	 */
	public static function get_context( $block ) {

		// clear each static context
		static::$context = array();

		// add a reference to the post
		static::$context['post'] = new \Timber\Post();

		// add a block ID in case needed for front end
		static::$context['id'] = static::$id;

		if ( $fields = get_field_objects() ) {
			foreach ( $fields as $name => $field ) {
				static::$context[ $field['name'] ] = $field['value'];
			}
		}

		static::$context['css_class']  = isset( $block['className'] ) ? $block['className'] : '';
		static::$context['align']      = isset( $block['align'] ) ? $block['align'] : '';
		static::$context['align_text'] = isset( $block['align_text'] ) ? $block['align_text'] : '';

		return static::$context;
	}

}
