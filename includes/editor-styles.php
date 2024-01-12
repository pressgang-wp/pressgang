<?php

/**
 * Class EditorStyles
 *
 * Handles the addition of custom styles to the WordPress editor.
 */
class EditorStyles {

	protected string $editor_styles_path = '/css/editor-styles.css';

	/**
	 * EditorStyles constructor.
	 *
	 * Adds an action hook to initialize editor styles.
	 */
	public function __construct( array $args ) {

		if ( isset( $args['path'] ) ) {
			$this->editor_styles_path = $args['path'];
		}

		add_action( 'admin_init', [ $this, 'add_editor_styles' ] );
	}

	/**
	 * Adds custom styles to the WordPress editor.
	 *
	 * This method enables theme support for editor styles and adds a specific stylesheet
	 * to the editor to ensure consistency between the editor and front-end styles.
	 */
	public function add_editor_styles() {
		add_theme_support( 'editor-styles' );
		add_editor_style( $this->editor_styles_path );
	}

}

new EditorStyles([]);
