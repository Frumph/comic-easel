<?php

/**
 * The archive listing queries — functions/shortcodes.php
 *
 * These functions build SQL by hand, and their $order and $chapter arguments arrive from
 * [comic-archive] shortcode attributes. A spy $wpdb records the SQL, which lets us assert
 * on the query text with no database at all — the plugin's queries use MySQL-only date
 * functions, so a SQLite test database would misreport them anyway.
 *
 * Characterization only: these assert the shape of the query, which does not change when
 * the arguments start being sanitised. The injection assertions ship with that fix.
 */
class ArchiveQueryTest extends CE_TestCase {

	/** @var CE_WPDB_Spy */
	private $wpdb;

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/shortcodes.php' );
		$this->wpdb = $this->useWpdbSpy();
		// Pin the year so the functions do not go looking for the newest comic.
		$_GET['archive_year'] = '2020';
	}

	protected function tearDown(): void {
		unset( $_GET['archive_year'] );
		parent::tearDown();
	}

	public function testByYearWithoutChapterQueriesPublishedComics() {
		ceo_archive_list_by_year( false, 'ASC', 0 );
		$sql = $this->wpdb->last();
		$this->assertStringContainsString( 'DISTINCT YEAR(post_date)', $sql );
		$this->assertStringContainsString( "post_type='comic'", $sql );
		$this->assertStringContainsString( "post_status = 'publish'", $sql );
	}

	/**
	 * The year selector has historically stayed chronological even when the posts inside
	 * the selected year are requested newest-first.
	 */
	public function testByYearWithoutChapterKeepsTheYearSelectorAscending() {
		ceo_archive_list_by_year( false, 'DESC', 0 );
		$this->assertStringContainsString( 'ORDER BY post_date ASC', $this->wpdb->last() );
	}

	public function testByYearWithChapterJoinsTheChaptersTaxonomy() {
		ceo_archive_list_by_year( false, 'ASC', 5 );
		$sql = $this->wpdb->last();
		$this->assertStringContainsString( 'term_relationships', $sql );
		$this->assertStringContainsString( 'term_taxonomy', $sql );
		$this->assertStringContainsString( "taxonomy = 'chapters'", $sql );
	}

	public function testByAllYearsWithChapterJoinsTheChaptersTaxonomy() {
		ceo_archive_list_by_all_years( false, 'ASC', 5 );
		$this->assertStringContainsString( "taxonomy = 'chapters'", $this->wpdb->last() );
	}

	public function testByAllYearsWithoutChapterHonorsDescendingOrder() {
		ceo_archive_list_by_all_years( false, 'DESC', 0 );
		$this->assertStringContainsString( 'ORDER BY post_date DESC', $this->wpdb->last() );
	}

	/**
	 * Table names must come from $wpdb rather than being hardcoded, or the query silently
	 * returns nothing on any site whose prefix is not the default.
	 */
	public function testArchiveQueriesUseTheConfiguredTablePrefix() {
		$this->wpdb->posts = 'xyz_posts';
		ceo_archive_list_by_year( false, 'ASC', 0 );
		$sql = $this->wpdb->last();
		$this->assertStringContainsString( 'xyz_posts', $sql );
		$this->assertStringNotContainsString( 'wp_posts', $sql );
	}

	public function testByYearRendersTheSelectedYearHeading() {
		$out = ceo_archive_list_by_year( false, 'ASC', 0 );
		$this->assertStringContainsString( '2020', $out );
		$this->assertStringContainsString( 'archive-yearlist', $out );
	}

	/**
	 * archive_year comes from the query string and is cast to int before use, so a
	 * non-numeric value must not reach the output.
	 */
	public function testArchiveYearFromQueryStringIsCastToInteger() {
		$_GET['archive_year'] = '2020<script>alert(1)</script>';
		$out = ceo_archive_list_by_year( false, 'ASC', 0 );
		$this->assertStringNotContainsString( '<script>', $out );
		$this->assertStringContainsString( '2020', $out );
	}

	public function testArchiveYearPreservesLegacySignedIntegerCoercion() {
		$_GET['archive_year'] = '-2020';
		$out = ceo_archive_list_by_year( false, 'ASC', 0 );

		$this->assertStringContainsString( '-2020', $out );
	}
}
