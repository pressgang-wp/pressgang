<?php

// TODO maybe deprecate

namespace PressGang\Configuration;

/**
 * Class Templates
 *
 * Manages custom page templates within the theme. This class allows for the registration
 * of custom templates specified in the theme's config files and ensures they
 * are recognized and used by WordPress when rendering pages.
 *
 * @package PressGang\Configuration
 */
class Templates extends ConfigurationSingleton {

	/**
	 * Directory name where the page templates are stored.
	 *
	 * @var string
	 */
	const TEMPLATES_FOLDER = 'page-templates';

	/**
	 * Initializes the class by setting up WordPress filters for handling custom page templates.
	 * Adds templates to the WordPress cache to make them available in the admin page attributes dropdown
	 * and ensures they are recognized during post saving.
	 *
	 * @param array $config Array of template file names to be registered.
	 */
	public function initialize( array $config ): void {
		$this->config = $config;
		\add_filter( 'page_attributes_dropdown_pages_args', [ $this, 'register_templates' ] );
		\add_filter( 'wp_insert_post_data', [ $this, 'register_templates' ] );
		\add_filter( 'template_include', [ $this, 'view_template' ] );
	}

	/**
	 * Registers custom page templates in the WordPress admin interface.
	 *
	 * This function updates WordPress's cache of page templates to include custom templates.
	 * It uses a cache key pattern matching that used internally by WordPress, ensuring
	 * that the theme's custom templates are recognized and used by WordPress.
	 *
	 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/ For more information on WordPress template hierarchy.
	 *
	 * @param array $atts Attributes for the page attributes dropdown, not modified by this function.
	 *
	 * @return array Original attributes passed to the function.
	 */
	public function register_templates( $atts ) {

		// Create the cache key used by WordPress for storing the page template cache
		$cache_key = 'page_templates-' . md5( \get_theme_root() . '/' . \get_stylesheet() );

		// Retrieve the current list of page templates from the active theme
		$templates = \wp_get_theme()->get_page_templates();

		// Initialize an empty array if no templates are found
		// This ensures $templates is always an array, even if empty
		if ( empty( $templates ) || ! is_array( $templates ) ) {
			$templates = [];
		}

		// Clear the existing templates cache
		\wp_cache_delete( $cache_key, 'themes' );

		// Merge existing templates with custom templates from this theme
		$templates = array_merge( $templates, $this->config );

		// Add the merged list of templates back to the WordPress cache with a 30-minute expiration
		\wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		// Return the original attributes unchanged
		return $atts;
	}

	/**
	 * Determines the appropriate template file for the current page.
	 *
	 * Checks if a custom template is assigned to the page and ensures the file exists,
	 * by also checking the TEMPLATES_FOLDER.
	 *
	 * If the file exists, its path is returned to render the page.
	 *
	 * Otherwise, it logs an error and returns the default template.
	 *
	 * @param string $template The path of the default template.
	 *
	 * @return string The path of the custom or default template.
	 */
	public function view_template( string $template ): string {

		global $post;

		if ( $post ) {
			$custom_template_slug = \get_post_meta( $post->ID, '_wp_page_template', true );

			if ( isset( $this->templates[ $custom_template_slug ] ) ) {
				$file = \get_template_directory() . '/' . self::TEMPLATES_FOLDER . '/' . $custom_template_slug;

				if ( file_exists( $file ) ) {
					return $file;
				} else {
					error_log( "Template file: '{$file}' is missing!" );
				}
			}
		}

		return $template;
	}

}
