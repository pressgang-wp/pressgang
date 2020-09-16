<?php

namespace PressGang;

class EditorStyles {

    /**
     * EditorStyle constructor.
     */
    public function __construct()
    {
        add_action('init', array($this, 'add_editor_styles'));
    }

    /**
     * add_editor_styles
     *
     * https://codex.wordpress.org/Editor_Style
     *
     * @hooked init
     */
    public function add_editor_styles() {
        add_theme_support('editor-styles');
        add_editor_style(get_stylesheet_directory_uri() . '/css/editor-styles.css' );
    }

}

new EditorStyles();