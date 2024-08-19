<?php

namespace PressGang\Configuration;

/**
 * Class CustomMenuItems
 *
 * Adds items to a given WordPress menu according to config settings in custom-menu-items.php
 *
 * @package PressGang\Snippets
 */
class CustomMenuItems extends ConfigurationSingleton {

	/**
	 * @var array
	 */
	private array $menus = [];

	/**
	 * @param array $config
	 */
	public function initialize( array $config ): void {

		// Don't do anything in the admin area
		if ( ! \is_admin() ) {

			// Index the menus by slug
			foreach ( $config as $slug => $menu_args ) {
				$this->menus[ $slug ] = [
					'parent_object_id' => $menu_args['parent_object_id'] ?? 0,
					'subitems'         => $menu_args['subitems'] ?? [],
				];
			}

			\add_filter( 'wp_get_nav_menu_items', [ $this, 'filter_nav_menu_items' ], 10, 2 );
		}
	}

	/**
	 * add_subitems_to_menu
	 *
	 * Adds custom items to a navigation menu
	 *
	 * @link http://teleogistic.net/2013/02/dynamically-add-items-to-a-wp_nav_menu-list/
	 * @link https://github.com/timber/timber/issues/200
	 *
	 * @param array $items
	 * @param \WP_Term $menu
	 *
	 * @return array
	 */
	public function filter_nav_menu_items( array $items, \WP_Term $menu ): array {

		// Check if the current menu has a configuration
		if ( ! isset( $this->menus[ $menu->slug ] ) ) {
			return $items;
		}

		$menu_config         = $this->menus[ $menu->slug ];
		$parent_menu_item_id = 0;

		foreach ( $items as $item ) {
			if ( $menu_config['parent_object_id'] == $item->object_id ) {
				$parent_menu_item_id = $item->ID;
				break;
			}
		}

		$menu_order = count( $items ) + 1;

		foreach ( $menu_config['subitems'] as $subitem ) {
			$menu_item_data = (object) [
				'ID'                    => mt_rand( 100000, 999999 ), // Generate a numeric ID
				'post_title'            => $subitem['text'],
				'post_name'             => sanitize_title( $subitem['text'] ),
				'guid'                  => $subitem['url'],
				'post_content'          => '', // Empty since we're dealing with a menu item
				'post_excerpt'          => '',
				'post_status'           => 'publish',
				'post_type'             => 'nav_menu_item',
				'post_author'           => get_current_user_id(),
				'post_parent'           => $parent_menu_item_id,
				'menu_order'            => $menu_order,
				'post_date'             => current_time( 'mysql' ),
				'post_date_gmt'         => current_time( 'mysql', 1 ),
				'post_modified'         => current_time( 'mysql' ),
				'post_modified_gmt'     => current_time( 'mysql', 1 ),
				'post_content_filtered' => '',
				'post_mime_type'        => '',
				'comment_status'        => 'closed',
				'ping_status'           => 'closed',
				'filter'                => 'raw', // Ensures the object is not sanitized
			];

			// Create the WP_Post object
			$menu_item_post = new \WP_Post( $menu_item_data );

			// Add additional meta fields specific to menu items
			$menu_item_post->menu_item_parent = $parent_menu_item_id;
			$menu_item_post->object_id        = 0;
			$menu_item_post->object           = 'custom';
			$menu_item_post->type             = 'custom';
			$menu_item_post->type_label       = __( 'Custom Link' );
			$menu_item_post->url              = $subitem['url'];
			$menu_item_post->classes          = isset( $subitem['classes'] ) ? $subitem['classes'] : [];
			$menu_item_post->target           = isset( $subitem['target'] ) ? $subitem['target'] : '';
			$menu_item_post->attr_title       = isset( $subitem['text'] ) ? $subitem['text'] : '';
			$menu_item_post->description      = isset( $subitem['description'] ) ? $subitem['description'] : '';
			$menu_item_post->xfn              = '';

			$items[] = $menu_item_post;
			$menu_order ++;
		}

		return $items;

	}
}
