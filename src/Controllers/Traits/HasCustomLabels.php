<?php

namespace PressGang\Controllers\Traits;

use Doctrine\Inflector\InflectorFactory;
use function Symfony\Component\String\u;

/**
 * Shared by CustomPostTypes and CustomTaxonomies to auto-generate labels from a key.
 */
trait HasCustomLabels {

	/**
	 * Merges auto-generated singular/plural labels into the registration args.
	 *
	 * @param string $key  The CPT or taxonomy slug from config.
	 * @param array  $args Registration arguments (may already contain partial labels).
	 *
	 * @return array The args with a complete 'labels' array merged in.
	 */
	protected function parse_labels( string $key, array $args ): array {

		$name = isset( $args['name'] ) ? $args['name'] : $key;
		$name = u( $name )
			->replace( '_', ' ' )
			->replace( '-', ' ' )
			->title( true )
			->toString();

		$inflector = InflectorFactory::create()->build();
		$plural    = $inflector->pluralize( $name );

		if ( ! isset( $args['name'] ) ) {
			$args['name'] = $name;
		}

		if ( ! isset( $args['labels'] ) ) {
			$args['labels'] = [];
		}

		$labels = [
			'name'          => $plural,
			'singular_name' => $name,
			'add_new_item'  => \_x( sprintf( "Add new %s", $name ), 'Labels', THEMENAME ),
			'search_items'  => \_x( sprintf( "Search %s", $name ), 'Labels', THEMENAME ),
			'all_items'     => \_x( sprintf( "All %s", $plural ), 'Labels', THEMENAME ),
			'edit_item'     => \_x( sprintf( "Edit %s", $name ), 'Labels', THEMENAME ),
			'update_item'   => \_x( sprintf( "Update %s", $name ), 'Labels', THEMENAME ),
			'new_item_name' => \_x( sprintf( "New %s", $name ), 'Labels', THEMENAME ),
			'menu_name'     => $plural,
		];

		$labels = \wp_parse_args( $args['labels'], $labels );

		$args['labels'] = $labels;

		return $args;
	}
}
