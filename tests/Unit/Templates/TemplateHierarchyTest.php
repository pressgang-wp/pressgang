<?php

namespace PressGang\Tests\Unit\Templates;

use PressGang\Templates\TemplateHierarchy;
use PressGang\Tests\Unit\TestCase;

/**
 * Tests hyphenated candidate injection and candidate recording.
 */
class TemplateHierarchyTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		TemplateHierarchy::reset();
	}

	/** @test */
	public function inserts_hyphenated_twin_before_underscored_candidate(): void {
		$hierarchy = new TemplateHierarchy();

		$this->assertSame(
			[ 'taxonomy-event-type.php', 'taxonomy-event_type.php', 'taxonomy.php' ],
			$hierarchy->filter_candidates( [ 'taxonomy-event_type.php', 'taxonomy.php' ] )
		);
	}

	/** @test */
	public function leaves_hyphen_free_candidates_untouched(): void {
		$hierarchy = new TemplateHierarchy();

		$this->assertSame(
			[ 'archive.php', 'index.php' ],
			$hierarchy->filter_candidates( [ 'archive.php', 'index.php' ] )
		);
	}

	/** @test */
	public function records_candidate_slugs_in_resolution_order(): void {
		$hierarchy = new TemplateHierarchy();

		$hierarchy->filter_candidates( [ 'taxonomy-event_type.php', 'taxonomy.php' ] );
		$hierarchy->filter_candidates( [ 'archive.php' ] );

		$this->assertSame(
			[ 'taxonomy-event-type', 'taxonomy-event_type', 'taxonomy', 'archive' ],
			TemplateHierarchy::candidates()
		);
	}

	/** @test */
	public function prepended_candidates_take_priority_and_dedupe(): void {
		$hierarchy = new TemplateHierarchy();

		$hierarchy->filter_candidates( [ '404.php' ] );
		TemplateHierarchy::prepend( 'taxonomy-event-type', '404' );

		$this->assertSame( [ 'taxonomy-event-type', '404' ], TemplateHierarchy::candidates() );
	}

	/** @test */
	public function skips_path_qualified_candidates_and_duplicates(): void {
		$hierarchy = new TemplateHierarchy();

		$hierarchy->filter_candidates( [ 'page-templates/grid-page.php', 'page.php' ] );
		$hierarchy->filter_candidates( [ 'page.php' ] );

		$this->assertSame( [ 'page' ], TemplateHierarchy::candidates() );
	}
}
