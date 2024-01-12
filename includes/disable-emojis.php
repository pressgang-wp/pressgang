<?php

/**
 * Disable Emojis
 *
 * Removes emoji support and related scripts from WordPress for performance optimization.
 */
class DisableEmojis {

	/**
	 * Constructor.
	 *
	 * Adds actions and filters to disable emojis in WordPress.
	 */
	public function __construct() {
		\add_action( 'init', [ $this, 'disable_emojis' ] );
		\add_filter( 'tiny_mce_plugins', [ $this, 'disable_emojis_tinymce' ] );
		\add_filter( 'wp_resource_hints', [ $this, 'disable_emojis_remove_dns_prefetch' ], 10, 2 );
	}

	/**
	 * Disables emoji support and related scripts.
	 */
	public function disable_emojis(): void {
		\remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		\remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		\remove_action( 'wp_print_styles', 'print_emoji_styles' );
		\remove_action( 'admin_print_styles', 'print_emoji_styles' );
		\remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		\remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		\remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}

	/**
	 * Removes the TinyMCE emoji plugin.
	 *
	 * @param array $plugins TinyMCE plugins.
	 *
	 * @return array Modified list of TinyMCE plugins.
	 */
	public function disable_emojis_tinymce( $plugins ): array {
		return is_array( $plugins ) ? array_diff( $plugins, [ 'wpemoji' ] ) : [];
	}

	/**
	 * Removes emoji CDN hostname from DNS prefetching hints.
	 *
	 * @param array $urls URLs to print for resource hints.
	 * @param string $relation_type The relation type the URLs are printed for.
	 *
	 * @return array Modified list of URLs.
	 */
	public function disable_emojis_remove_dns_prefetch( array $urls, string $relation_type ): array {
		if ( $relation_type == 'dns-prefetch' ) {
			$emoji_svg_url = \apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
			$urls          = array_diff( $urls, [ $emoji_svg_url ] );
		}

		return $urls;
	}
}

new DisableEmojis();
