<?php

namespace PressGang;

use \Timber\Timber;

require_once( 'loader.php' );

/**
 * Class Site
 *
 * @package PressGang
 */
class Site extends \Timber\Site {

	public $stylesheet;
	public $keywords;
	public $logo;
	public $copyright;
	private static $meta_description;

	/**
	 *__construct
	 *
	 * @param string|int $site_name_or_id
	 */
	function __construct( $site_name_or_id = null ) {

		Timber::init();

		// load all customizer mods
		if ( $theme_mods = get_theme_mods() ) {
			foreach ( $theme_mods as $mod_key => &$mod_val ) {
				$this->$mod_key = apply_filters( $mod_key, $mod_val );
			}
		}

		/*
		$keywords = wp_cache_get('site_keywords');

		if (!$keywords) {
			// add custom params
			$keywords = apply_filters('site_keywords', implode(', ', array_map(function ($tag) {
				return $tag->name;
			}, get_tags(array('orderby' => 'count', 'order' => 'DESC', 'number' => 20)))));

			wp_cache_set('site_keywords', $keywords);
		}

		$this->keywords = $keywords;
		*/

		// get site email
		$this->email = get_option( 'admin_email' );

		// get stylesheet (can be set in customizer theme mod)
		$this->stylesheet = $this->stylesheet ?: 'styles.css';
		$this->stylesheet = sprintf( "%s/css/%s?v=%s",
			get_stylesheet_directory_uri(), $this->stylesheet,
			filemtime( get_stylesheet_directory() . "/css/{$this->stylesheet}" ) );

		add_filter( 'timber/context', [ $this, 'add_to_context' ] );
		add_filter( 'timber/twig', [ $this, 'add_to_twig' ] );

		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'timber/context',
				[ $this, 'add_woocommerce_to_context' ] );
		}

		// add a theme color
		$this->theme_color = Config::get( 'theme-color' );

		// disable default Yoast meta description (we'll add it ourselves)
		add_filter( 'wpseo_metadesc', function () {
			return false;
		} );

		parent::__construct( $site_name_or_id );
	}

	/**
	 * add_to_context
	 *
	 * @param $context
	 *
	 * @return mixed
	 */
	public function add_to_context( $context ) {
		$context['site'] = $this;

		$context = \apply_filters( "site_context", $context );

		return $context;
	}

	/**
	 * add_woocommerce_to_context
	 *
	 * @param $context
	 *
	 * @return mixed
	 */
	public function add_woocommerce_to_context( $context ) {

		global $woocommerce;

		$account_page_id = get_option( 'woocommerce_myaccount_page_id' );

		$context['my_account_link']     = get_permalink( $account_page_id );
		$context['logout_link']         = wp_logout_url( get_permalink( $account_page_id ) );
		$context['cart_link']           = wc_get_cart_url();
		$context['checkout_link']       = wc_get_checkout_url();
		$context['cart_contents_count'] = WC()->cart->get_cart_contents_count();

		return $context;
	}

	/**
	 * add_to_twig
	 *
	 * Add Custom Functions to Twig
	 */
	public function add_to_twig( $twig ) {
		$twig->addFunction( new \Twig\TwigFunction( 'esc_attr', 'esc_attr' ) );
		$twig->addFunction( new \Twig\TwigFunction( 'esc_url', 'esc_url' ) );
		$twig->addFunction( new \Twig\TwigFunction( 'get_search_query',
			'get_search_query' ) );

		$twig->addFunction( new \Twig\TwigFunction( 'meta_description', [
			'PressGang\Site',
			'meta_description',
		] ) );

		$twig->addFunction( new \Twig\TwigFunction( 'get_option',
			'get_option' ) );

		$twig->addFunction( new \Twig\TwigFunction( 'get_theme_mod',
			'get_theme_mod' ) );

		if ( class_exists( 'WooCommerce' ) ) {
			$twig->addFunction( new \Twig\TwigFilter( 'timber_set_product', [
				'PressGang\Site',
				'timber_set_product',
			] ) );
		}

		// TODO can we lazy load or include?

		$twig->addFilter( new \Twig\TwigFilter( 'pluralize',
			[ 'PressGang\Pluralizer', 'pluralize' ] ) );

		// add text-domain to global
		$twig->addGlobal( 'THEMENAME', THEMENAME );

		return $twig;
	}

	/**
	 * add meta_description
	 *
	 * hook after post has loaded to add a unique meta-description
	 *
	 */
	public static function meta_description() {

		$description = self::$meta_description;

		if ( empty( $description ) ) {

			if ( $object = get_queried_object() ) {

				$key = sprintf( "meta_description_%s_%s",
					strtolower( get_class( $object ) ), $object->ID ?? $object->name );

				if ( ! $description = wp_cache_get( $key ) ) {

					if ( is_single() || is_page() ) {

						// try Yoast
						$description = get_post_meta( $object->ID,
							'_yoast_wpseo_metadesc', true );

						if ( empty( $description ) ) {

							$post = \Timber::get_post();

							// check for custom field
							$description = wptexturize( $post->get_field( 'meta_description' ) );

							// else use excerpt
							if ( empty( $description ) ) {
								$description = $post->post_excerpt;
							}

							// else use preview
							if ( empty( $description ) ) {
								$description = strip_shortcodes( $post->post_content );
							}
						}

					}

					if ( is_tax() ) {

						// try Yoast

						if ( $yoast_meta = get_option( 'wpseo_taxonomy_meta' ) ) {
							$description = $yoast_meta[ $object->taxonomy ][ $object->term_id ]['wpseo_desc'];
						}

						if ( empty( $description ) ) {

							if ( $temp = term_description( get_queried_object(),
								get_query_var( 'taxonomy' ) ) ) {
								$description = $temp;
							}
						}

					} elseif ( is_post_type_archive() ) {
						if ( $temp = get_the_archive_description() ) {
							$description = $temp;
						}
					}

					$description = esc_attr( wp_strip_all_tags( $description ) );

					// finally use the blog description
					if ( empty( $description ) ) {
						$description = get_bloginfo( 'description', 'raw' );
					}

					// limit to SEO recommended length
					if ( strlen( $description ) > 155 ) {
						$description = mb_substr( $description, 0, 155 );
						$description = \Timber\TextHelper::trim_words( $description,
							str_word_count( $description ) - 1 );
					}

					self::$meta_description = $description;
					wp_cache_set( $key, self::$meta_description );
				}

			}
		}

		$description = apply_filters( 'meta_description', $description );

		return $description;
	}

	/**
	 * timber_set_product
	 *
	 * Set the timber post context for WooCommerce teaser-product.twig
	 */
	public static function timber_set_product( $post ) {
		global $product;
		$product = wc_get_product( $post->ID );

		return $product;
	}

}

new Site();
