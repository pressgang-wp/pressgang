<?php

namespace PressGang\Tests\Unit\Configuration;

use Brain\Monkey\Functions;
use PressGang\Configuration\CustomPostTypes;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests CustomPostTypes configuration class: init hook registration,
 * label auto-generation via HasCustomLabels, per-CPT filters, and
 * registration with WordPress.
 */
class CustomPostTypesTest extends TestCase {

	protected function set_up(): void {
		parent::set_up();
		$this->resetSingletonInstances();
	}

	protected function tear_down(): void {
		$this->resetSingletonInstances();
		parent::tear_down();
	}

	/**
	 * Common stubs for HasCustomLabels dependencies.
	 */
	private function stubLabelHelpers(): void {
		Functions\expect( '_x' )->andReturnUsing( function ( $text ) {
			return $text;
		} );
	}

	/**
	 * Default apply_filters pass-through.
	 */
	private function stubApplyFilters(): void {
		Functions\expect( 'apply_filters' )->andReturnUsing( function () {
			return func_get_args()[1];
		} );
	}

	/** @test */
	public function initialize_registers_init_action(): void {
		Functions\expect( 'add_action' )
			->once()
			->with( 'init', \Mockery::type( 'array' ) );

		$cpt = CustomPostTypes::get_instance();
		$cpt->initialize( [ 'portfolio' => [ 'public' => true ] ] );
	}

	/** @test */
	public function register_cpt_calls_register_post_type(): void {
		Functions\expect( 'add_action' )->once();

		$config = [
			'portfolio' => [ 'public' => true ],
		];

		$cpt = CustomPostTypes::get_instance();
		$cpt->initialize( $config );

		$this->stubLabelHelpers();
		$this->stubApplyFilters();

		Functions\expect( 'register_post_type' )
			->once()
			->with( 'portfolio', \Mockery::on( function ( $args ) {
				return $args['public'] === true
					&& isset( $args['labels'] )
					&& $args['labels']['singular_name'] === 'Portfolio'
					&& $args['labels']['name'] === 'Portfolios';
			} ) );

		$cpt->register_custom_post_types();
	}

	/** @test */
	public function auto_generates_labels_from_hyphenated_key(): void {
		Functions\expect( 'add_action' )->once();

		$config = [
			'team-member' => [ 'public' => true ],
		];

		$cpt = CustomPostTypes::get_instance();
		$cpt->initialize( $config );

		$this->stubLabelHelpers();
		$this->stubApplyFilters();

		Functions\expect( 'register_post_type' )
			->once()
			->with( 'team-member', \Mockery::on( function ( $args ) {
				return $args['labels']['singular_name'] === 'Team Member'
					&& $args['labels']['name'] === 'Team Members'
					&& str_contains( $args['labels']['add_new_item'], 'Team Member' );
			} ) );

		$cpt->register_custom_post_types();
	}

	/** @test */
	public function explicit_name_overrides_key_for_labels(): void {
		Functions\expect( 'add_action' )->once();

		$config = [
			'faq' => [
				'name'   => 'FAQ',
				'public' => true,
			],
		];

		$cpt = CustomPostTypes::get_instance();
		$cpt->initialize( $config );

		$this->stubLabelHelpers();
		$this->stubApplyFilters();

		Functions\expect( 'register_post_type' )
			->once()
			->with( 'faq', \Mockery::on( function ( $args ) {
				return $args['labels']['singular_name'] === 'FAQ';
			} ) );

		$cpt->register_custom_post_types();
	}

	/** @test */
	public function existing_labels_are_preserved_via_wp_parse_args(): void {
		Functions\expect( 'add_action' )->once();

		$config = [
			'event' => [
				'public' => true,
				'labels' => [
					'menu_name' => 'Custom Menu Name',
				],
			],
		];

		$cpt = CustomPostTypes::get_instance();
		$cpt->initialize( $config );

		$this->stubLabelHelpers();
		$this->stubApplyFilters();

		Functions\expect( 'register_post_type' )
			->once()
			->with( 'event', \Mockery::on( function ( $args ) {
				return $args['labels']['menu_name'] === 'Custom Menu Name'
					&& $args['labels']['singular_name'] === 'Event';
			} ) );

		$cpt->register_custom_post_types();
	}

	/** @test */
	public function filter_can_rename_post_type_key(): void {
		Functions\expect( 'add_action' )->once();

		$config = [
			'project' => [ 'public' => true ],
		];

		$cpt = CustomPostTypes::get_instance();
		$cpt->initialize( $config );

		$this->stubLabelHelpers();

		// Dynamic handling: rename key filter, pass through args filter
		Functions\expect( 'apply_filters' )->andReturnUsing( function ( $hook, $value ) {
			if ( $hook === 'pressgang_cpt_project' ) {
				return 'portfolio';
			}
			return $value;
		} );

		Functions\expect( 'register_post_type' )
			->once()
			->with( 'portfolio', \Mockery::type( 'array' ) );

		$cpt->register_custom_post_types();
	}

	/** @test */
	public function registers_multiple_post_types(): void {
		Functions\expect( 'add_action' )->once();

		$config = [
			'portfolio' => [ 'public' => true ],
			'event'     => [ 'public' => true ],
		];

		$cpt = CustomPostTypes::get_instance();
		$cpt->initialize( $config );

		$this->stubLabelHelpers();
		$this->stubApplyFilters();

		Functions\expect( 'register_post_type' )->twice();

		$cpt->register_custom_post_types();
	}
}
