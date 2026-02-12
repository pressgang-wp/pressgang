<?php

namespace PressGang\Traits;

use ReflectionMethod;

/**
 * Trait HandlesDynamicGetters
 *
 * Bridges PHP getter methods to Twig property access for Timber 2 model classes.
 * Models using this trait can define `get_{property}()` methods that are automatically
 * called when accessing `$model->property` in PHP or `{{ model.property }}` in Twig.
 *
 * @package PressGang\Traits
 */
trait HandlesDynamicGetters {

	/**
	 * Intercepts property access to check for custom getter methods.
	 *
	 * Signature intentionally untyped to match Timber's Post::__get($field)
	 * and Term::__get($field) for PHP 8 compatibility.
	 *
	 * @param string $field
	 *
	 * @return mixed
	 */
	public function __get( $field ) {
		if ( $this->has_custom_getter( $field ) ) {
			return $this->call_custom_getter( $field );
		}

		return parent::__get( $field );
	}

	/**
	 * Intercepts meta calls to check for custom getter methods.
	 *
	 * Signature intentionally untyped to match Timber's CoreEntity::meta($field_name, $args)
	 * for PHP 8 compatibility.
	 *
	 * @param string $field_name
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public function meta( $field_name = '', $args = [] ) {
		if ( $field_name && $this->has_custom_getter( $field_name ) ) {
			return $this->call_custom_getter( $field_name );
		}

		return parent::meta( $field_name, $args );
	}

	/**
	 * Checks whether a custom getter method exists for the given field.
	 *
	 * @param string $field
	 *
	 * @return bool
	 */
	protected function has_custom_getter( string $field ): bool {
		return method_exists( $this, "get_{$field}" );
	}

	/**
	 * Calls the custom getter method for the given field.
	 *
	 * @param string $field
	 *
	 * @return mixed
	 */
	protected function call_custom_getter( string $field ): mixed {
		$method = "get_{$field}";

		$reflection = new ReflectionMethod( $this, $method );
		$parameters = $reflection->getParameters();
		$resolved   = $this->default_parameter_resolution( $parameters );

		return $this->$method( ...$resolved );
	}

	/**
	 * Resolves default values for method parameters.
	 *
	 * @param \ReflectionParameter[] $parameters
	 *
	 * @return array
	 */
	protected function default_parameter_resolution( array $parameters ): array {
		$resolved = array();

		foreach ( $parameters as $param ) {
			if ( $param->isDefaultValueAvailable() ) {
				$resolved[] = $param->getDefaultValue();
			}
		}

		return $resolved;
	}
}
