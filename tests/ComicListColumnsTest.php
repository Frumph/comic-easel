<?php

/**
 * The Comics list's Chapter, Characters and Location columns — functions/admin-meta.php
 *
 * Each term in those columns links to the Comics list filtered by that term. The filter takes the
 * term's slug; its name only matched when the two happened to be the same.
 */
class ComicListColumnsTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/admin-meta.php' );
	}

	private function column( $column, $taxonomy ) {
		CE_Test_State::$terms[ $taxonomy ] = array( $this->makeTerm( 4, 'the-old-mill', 'The Old Mill', $taxonomy ) );
		return $this->capture( function () use ( $column ) {
			ceo_manage_comic_columns( $column, 7 );
		} );
	}

	public function testTermsLinkToTheFilteredComicsList() {
		$this->assertSame( CE_KSES_SENTINEL . '<a href="https://example.test/wp-admin/edit.php?post_type=comic&#038;chapters=the-old-mill">The Old Mill</a>', $this->column( 'chapter', 'chapters' ) );
		$this->assertSame( CE_KSES_SENTINEL . '<a href="https://example.test/wp-admin/edit.php?post_type=comic&#038;characters=the-old-mill">The Old Mill</a>', $this->column( 'characters', 'characters' ) );
	}

	/**
	 * admin_url() already ends in /wp-admin/, and the Location links used to add another,
	 * which led to a missing page.
	 */
	public function testLocationsLinkToTheFilteredComicsList() {
		$this->assertSame( CE_KSES_SENTINEL . '<a href="https://example.test/wp-admin/edit.php?post_type=comic&#038;locations=the-old-mill">The Old Mill</a>', $this->column( 'locations', 'locations' ) );
	}
}
