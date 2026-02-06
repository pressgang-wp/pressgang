<?php

namespace PressGang\Tests\Unit\Blocks;

use PressGang\Blocks\BlockClassManager;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests BlockClassManager::get_css_classes() — a pure function with no WP dependencies.
 */
class BlockClassManagerTest extends TestCase {

	/** @test */
	public function empty_block_returns_no_classes(): void {
		$this->assertSame( [], BlockClassManager::get_css_classes( [] ) );
	}

	/** @test */
	public function class_name_is_included(): void {
		$block = [ 'className' => 'my-custom-class' ];
		$this->assertContains( 'my-custom-class', BlockClassManager::get_css_classes( $block ) );
	}

	/** @test */
	public function background_color_generates_two_classes(): void {
		$block   = [ 'backgroundColor' => 'primary' ];
		$classes = BlockClassManager::get_css_classes( $block );

		$this->assertContains( 'primary', $classes );
		$this->assertContains( 'has-primary-background-color', $classes );
	}

	/** @test */
	public function text_color_generates_has_color_class(): void {
		$block   = [ 'textColor' => 'white' ];
		$classes = BlockClassManager::get_css_classes( $block );

		$this->assertContains( 'has-white-color', $classes );
	}

	/** @test */
	public function align_generates_alignment_class(): void {
		$block   = [ 'align' => 'full' ];
		$classes = BlockClassManager::get_css_classes( $block );

		$this->assertContains( 'align-full', $classes );
	}

	/** @test */
	public function empty_align_is_ignored(): void {
		$block   = [ 'align' => '' ];
		$classes = BlockClassManager::get_css_classes( $block );

		$this->assertEmpty( $classes );
	}

	/** @test */
	public function acf_text_alignment_is_used(): void {
		$block   = [ 'alignText' => 'center' ];
		$classes = BlockClassManager::get_css_classes( $block );

		$this->assertContains( 'has-text-align-center', $classes );
	}

	/** @test */
	public function core_text_alignment_from_style_is_used(): void {
		$block   = [ 'style' => [ 'typography' => [ 'textAlign' => 'right' ] ] ];
		$classes = BlockClassManager::get_css_classes( $block );

		$this->assertContains( 'has-text-align-right', $classes );
	}

	/** @test */
	public function acf_text_alignment_takes_precedence_over_core(): void {
		$block = [
			'alignText' => 'left',
			'style'     => [ 'typography' => [ 'textAlign' => 'right' ] ],
		];

		$classes = BlockClassManager::get_css_classes( $block );

		$this->assertContains( 'has-text-align-left', $classes );
		$this->assertNotContains( 'has-text-align-right', $classes );
	}

	/** @test */
	public function all_attributes_combined(): void {
		$block = [
			'className'       => 'hero-block',
			'backgroundColor' => 'dark',
			'textColor'       => 'light',
			'align'           => 'wide',
			'alignText'       => 'center',
		];

		$classes = BlockClassManager::get_css_classes( $block );

		$this->assertSame( [
			'hero-block',
			'dark',
			'has-dark-background-color',
			'has-light-color',
			'align-wide',
			'has-text-align-center',
		], $classes );
	}
}
