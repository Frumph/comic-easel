<?php

/**
 * ceo_get_character_list() — functions/shortcodes.php
 *
 * Backs [cast-page chapter=N]. Takes an untrusted shortcode attribute and builds SQL from
 * it, so it is worth pinning what the query is supposed to select.
 *
 * Note there is deliberately no assertion here that the table names come from $wpdb: on
 * master this function hardcodes the default prefix, and the test for that ships with the
 * commit that fixes it.
 */
class CharacterListTest extends CE_TestCase {

	/** @var CE_WPDB_Spy */
	private $wpdb;

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/shortcodes.php' );
		$this->wpdb = $this->useWpdbSpy();
	}

	public function testReturnsFalseWhenNoCharactersFound() {
		$this->wpdb->result = array();
		$this->assertFalse( ceo_get_character_list( 5 ) );
	}

	public function testReturnsTheRowsWhenCharactersExist() {
		$row              = new stdClass();
		$row->tag         = 'hero';
		$this->wpdb->result = array( $row );
		$this->assertSame( array( $row ), ceo_get_character_list( 5 ) );
	}

	public function testQuerySelectsCharacterNamesForPublishedComicsInAChapter() {
		ceo_get_character_list( 5 );
		$sql = $this->wpdb->last();
		$this->assertStringContainsString( "t1.taxonomy = 'chapters'", $sql );
		$this->assertStringContainsString( "t2.taxonomy = 'characters'", $sql );
		$this->assertStringContainsString( "p1.post_status = 'publish'", $sql );
		$this->assertStringContainsString( "p2.post_status = 'publish'", $sql );
		$this->assertStringContainsString( 'p1.ID = p2.ID', $sql );
	}

	public function testChapterArgumentReachesTheQuery() {
		ceo_get_character_list( 42 );
		$this->assertStringContainsString( '42', $this->wpdb->last() );
	}
}
