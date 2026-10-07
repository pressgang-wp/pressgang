<?php

namespace PressGang\Tests\Unit\Configuration;

use Brain\Monkey\Functions;
use PressGang\Configuration\BlockEditor;
use PressGang\Tests\Unit\TestCase;

class SupportedBlockEditor extends BlockEditor {
	public function __construct( private bool $supported ) {}
	protected function supports_profiles(): bool { return $this->supported; }
}

class BlockEditorTest extends TestCase {
	protected function set_up(): void {
		parent::set_up();
		$this->resetSingletonInstances();
		Functions\when( 'get_stylesheet_directory' )->justReturn( '/themes/child' );
		Functions\when( 'get_template_directory' )->justReturn( '/themes/parent' );
		Functions\when( 'wp_normalize_path' )->returnArg();
	}

	protected function tear_down(): void {
		$this->resetSingletonInstances();
		parent::tear_down();
	}

	private function makeEditor( array $config, bool $supported = true ): BlockEditor {
		$editor = new SupportedBlockEditor( $supported );
		$editor->initialize( $config );
		return $editor;
	}

	private function metadata( string $file = '/themes/child/blocks/hero/block.json' ): array {
		return [ 'file' => $file, 'name' => 'acf/hero', 'apiVersion' => 2,
			'acf' => [ 'mode' => 'edit', 'blockVersion' => 2, 'renderCallback' => [ 'App\\Blocks\\Hero', 'render' ] ],
			'supports' => [ 'anchor' => true, 'mode' => false ] ];
	}

	/** @test */
	public function defaults_leave_registration_untouched(): void {
		Functions\expect( 'add_filter' )->never();
		$editor = $this->makeEditor( [] );
		$this->assertSame( $this->metadata(), $editor->apply_acf_profile( $this->metadata() ) );
	}

	/** @test */
	public function form_profile_migrates_child_and_parent_without_replacing_renderers(): void {
		$editor = $this->makeEditor( [ 'acf_profile' => 'form' ] );
		foreach ( [ '/themes/child/blocks/hero/block.json', '/themes/parent/blocks/hero/block.json' ] as $file ) {
			$before = $this->metadata( $file );
			$after = $editor->apply_acf_profile( $before );
			$this->assertSame( 3, $after['apiVersion'] );
			$this->assertSame( 3, $after['acf']['blockVersion'] );
			$this->assertFalse( $after['acf']['renderPreview'] );
			$this->assertTrue( $after['acf']['hideFieldsInSidebar'] );
			$this->assertSame( 'preview', $after['acf']['mode'] );
			$this->assertSame( $before['acf']['renderCallback'], $after['acf']['renderCallback'] );
			$this->assertSame( $before['supports'], $after['supports'] );
		}
	}

	/** @test */
	public function plugin_core_and_other_theme_metadata_are_unchanged(): void {
		$editor = $this->makeEditor( [ 'acf_profile' => 'form' ] );
		foreach ( [ '/plugins/example/block.json', '/themes/child-other/block.json', '/themes/other/block.json' ] as $file ) {
			$before = $this->metadata( $file );
			$this->assertSame( $before, $editor->apply_acf_profile( $before ) );
		}
		$core = $this->metadata();
		unset( $core['acf'] );
		$this->assertSame( $core, $editor->apply_acf_profile( $core ) );
	}

	/** @test */
	public function per_block_profiles_and_opt_outs_take_precedence(): void {
		$before = $this->metadata();
		$editor = $this->makeEditor( [ 'acf_profile' => 'form', 'acf_blocks' => [ 'acf/hero' => 'preview' ] ] );
		$this->assertTrue( $editor->apply_acf_profile( $before )['acf']['renderPreview'] );
		$editor = $this->makeEditor( [ 'acf_profile' => 'form', 'acf_blocks' => [ 'acf/hero' => null ] ] );
		$this->assertSame( $before, $editor->apply_acf_profile( $before ) );
	}

	/** @test */
	public function explicit_ui_settings_survive_and_unsupported_acf_is_unchanged(): void {
		$before = $this->metadata();
		$before['acf']['renderPreview'] = true;
		$before['acf']['hideFieldsInSidebar'] = false;
		$editor = $this->makeEditor( [ 'acf_profile' => 'form' ] );
		$after = $editor->apply_acf_profile( $before );
		$this->assertTrue( $after['acf']['renderPreview'] );
		$this->assertFalse( $after['acf']['hideFieldsInSidebar'] );
		$editor = $this->makeEditor( [ 'acf_profile' => 'form' ], false );
		$this->assertSame( $before, $editor->apply_acf_profile( $before ) );
	}
}
