<?php

/**
 * The comic navigation bar — ceo_display_comic_navigation() in functions/injections.php
 */
class ComicNavBarTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/injections.php' );
		CE_Test_State::$pluginfo['enable_random_nav'] = true;
		$this->setGlobalPost( 7 );
	}

	private function randomLink() {
		$bar = $this->capture( function () {
			ceo_display_comic_navigation();
		} );
		$this->assertSame( 1, preg_match( '#<a href="([^"]*)" class="comic-nav-random"#', $bar, $link ) );
		return $link[1];
	}

	/**
	 * The link used to be the site address with ?random&nocache=1 stuck straight on: no slash
	 * before the query string, and a bare ampersand in the attribute.
	 */
	public function testRandomLinkGoesToTheHomePageWithAnEscapedQuery() {
		$this->assertSame( 'https://example.test/?random&#038;nocache=1', $this->randomLink() );
	}

	public function testRandomLinkCanStayInTheChapter() {
		CE_Test_State::$pluginfo['enable_chapter_only_random'] = true;
		CE_Test_State::$terms['chapters'] = array( $this->makeTerm( 3, 'book-one', 'Book One' ) );

		$this->assertSame( 'https://example.test/?random&#038;nocache=1&#038;stay=3', $this->randomLink() );
	}
}
