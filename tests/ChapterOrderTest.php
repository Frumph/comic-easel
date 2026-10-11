<?php

/**
 * Chapter order — functions/admin-meta.php
 *
 * A chapter's Order field is written straight to the menu_order column of the terms table,
 * when a chapter is created (create_chapters) and when it is edited or quick-edited
 * (edited_chapters), and only for a user who may edit chapters.
 */
class ChapterOrderTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/admin-meta.php' );
		$_POST = array( 'ceo_chapter_order' => '7' );
	}

	protected function tearDown(): void {
		$_POST = array();
		parent::tearDown();
	}

	public function testANewChapterGetsItsOrder() {
		$wpdb = $this->useWpdbSpy();
		$this->grantCurrentUserCap( 'manage_categories' );

		ceo_chapters_add_edit_menu_order( 12 );

		$this->assertSame( array( array( 'wp_terms', array( 'menu_order' => 7 ), array( 'term_id' => 12 ) ) ), $wpdb->updates );
	}

	public function testAnEditedChapterGetsItsOrder() {
		$wpdb = $this->useWpdbSpy();
		$this->grantCurrentUserCap( 'manage_categories' );
		$_POST['ceo_chapter_order'] = '12';

		ceo_chapters_save_value( 12, 12 );

		$this->assertSame( array( array( 'wp_terms', array( 'menu_order' => 12 ), array( 'term_id' => 12 ) ) ), $wpdb->updates );
	}

	public function testNothingIsWrittenForAUserWhoCannotEditChapters() {
		$wpdb = $this->useWpdbSpy();

		ceo_chapters_add_edit_menu_order( 12 );
		ceo_chapters_save_value( 12, 12 );

		$this->assertSame( array(), $wpdb->updates );
	}

	public function testNothingIsWrittenWithoutAnOrderField() {
		$wpdb = $this->useWpdbSpy();
		$this->grantCurrentUserCap( 'manage_categories' );
		$_POST = array();

		ceo_chapters_add_edit_menu_order( 12 );
		ceo_chapters_save_value( 12, 12 );

		$this->assertSame( array(), $wpdb->updates );
	}

	/**
	 * Hooked to the chapters taxonomy's own creation hook, not create_term, which fires for
	 * every taxonomy: adding a tag or category no longer runs it.
	 */
	public function testTheOrderIsSavedFromTheChapterHooksOnly() {
		$source = file_get_contents( CE_PLUGIN_DIR . '/functions/admin-meta.php' );
		$this->assertStringContainsString( "add_action('create_chapters', 'ceo_chapters_add_edit_menu_order');", $source );
		$this->assertStringContainsString( "add_action('edited_chapters', 'ceo_chapters_save_value', 10, 2);", $source );
		$this->assertSame( 0, preg_match( "/add_action\\(\\s*'create_term'/", $source ) );
	}
}
