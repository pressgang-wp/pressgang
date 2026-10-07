<?php

namespace PressGang\Configuration;

/**
 * Applies opt-in ACF v3 editing profiles from config/block-editor.php.
 * Only metadata belonging to the active child/parent theme is changed; plugin
 * blocks and themes that have not opted in retain their registration settings.
 */
class BlockEditor extends ConfigurationSingleton {

	/**
	 * @param array<array-key, mixed> $config
	 * @return void
	 */
	#[\Override]
	public function initialize( array $config ): void {
		$this->config = $config;
		if ( ! empty( $config['acf_profile'] ) || ! empty( $config['acf_blocks'] ) ) {
			\add_filter( 'block_type_metadata', [ $this, 'apply_acf_profile' ] );
		}
	}

	/**
	 * Migrates theme-owned ACF metadata before WordPress and ACF derive settings.
	 * Explicit preview/sidebar choices in block.json override profile defaults.
	 * A per-block null profile leaves the entire registration unchanged.
	 *
	 * @param array<string, mixed> $metadata
	 * @return array<string, mixed>
	 */
	public function apply_acf_profile( array $metadata ): array {
		if ( ! isset( $metadata['acf'] ) || ! is_array( $metadata['acf'] ) || ! $this->owns_metadata( $metadata ) ) {
			return $metadata;
		}

		$profile = $this->config['acf_profile'] ?? null;
		$blocks  = $this->config['acf_blocks'] ?? [];
		$name    = $metadata['name'] ?? '';
		if ( is_array( $blocks ) && is_string( $name ) && array_key_exists( $name, $blocks ) ) {
			$profile = $blocks[ $name ];
		}

		if ( ! in_array( $profile, [ 'form', 'preview' ], true ) || ! $this->supports_profiles() ) {
			return $metadata;
		}

		$metadata['apiVersion'] = 3;
		$metadata['acf'] = array_replace(
			[ 'renderPreview' => 'preview' === $profile, 'hideFieldsInSidebar' => true ],
			$metadata['acf'],
			[ 'blockVersion' => 3, 'mode' => 'preview' ]
		);

		return $metadata;
	}

	/**
	 * @param array<string, mixed> $metadata
	 * @return bool
	 */
	private function owns_metadata( array $metadata ): bool {
		$file = $metadata['file'] ?? null;
		if ( ! is_string( $file ) ) {
			return false;
		}

		$file = \wp_normalize_path( $file );
		foreach ( [ \get_stylesheet_directory(), \get_template_directory() ] as $directory ) {
			if ( str_starts_with( $file, rtrim( \wp_normalize_path( $directory ), '/' ) . '/' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Older ACF releases cannot implement the no-preview v3 editing profile.
	 * Kept as a seam so compatibility behaviour can be tested without ACF loaded.
	 *
	 * @return bool
	 */
	protected function supports_profiles(): bool {
		return \defined( 'ACF_VERSION' ) && version_compare( (string) \constant( 'ACF_VERSION' ), '6.8.9', '>=' );
	}
}
