<?php

/**
 * Graphical Navigation widget, render side — widgets/navigation.php
 *
 * WidgetSettingsTest covers what update() stores; this covers what the widget prints.
 */
class NavigationWidgetTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'widgets/navigation.php' );
	}

	private function render( $instance ) {
		$widget = new ceo_comic_navigation_widget();
		return $this->capture( function () use ( $widget, $instance ) {
			$widget->widget( self::widgetArgs(), $instance );
		} );
	}

	public function testRendersAnEmptyInstance() {
		// A Legacy Widget block that was never saved renders with no settings at all: every
		// link off, which leaves the empty table.
		CE_Test_State::$pluginfo['enable_buy_comic'] = true;
		$this->setGlobalPost( 7 );
		$out = $this->render( array() );

		$this->assertStringContainsString( '<table class="comic_navi">', $out );
		$this->assertStringNotContainsString( 'class="navi', $out );
	}

	/**
	 * A widget saved through its form with every link switched on and the default labels,
	 * which update() stores attribute-escaped.
	 */
	private function everythingOn() {
		$form = array( 'archive_path' => 'https://example.test/archive/' );
		foreach ( array( 'previous_chap', 'next_chap', 'first_in', 'last_in', 'previous_in', 'next_in', 'first', 'last', 'previous', 'next', 'random', 'archives', 'comments', 'comictitle', 'comicchapter', 'imageurl', 'buycomic' ) as $key ) {
			$form[ $key ] = '1';
		}
		$labels = array(
			'previous_chap_title' => '<[ Prev Chapter', 'next_chap_title' => 'Next Chapter ]>',
			'first_in_title' => '<<[ First', 'last_in_title' => 'Last ]>>',
			'previous_in_title' => '<[ Prev', 'next_in_title' => 'Next ]>',
			'first_title' => '<< First', 'last_title' => 'Last >>',
			'previous_title' => '< Prev', 'next_title' => 'Next >',
			'random_title' => 'Random', 'archives_title' => 'Archives',
			'comments_title' => 'Comments', 'buycomic_title' => 'Buy & "Own"',
		);
		$widget = new ceo_comic_navigation_widget();
		return $widget->update( array_merge( $form, $labels ), array() );
	}

	private function onAComicWithNeighbours() {
		CE_Test_State::$pluginfo['enable_buy_comic'] = true;
		CE_Test_State::$pluginfo['buy_comic_url']    = 'https://example.test/buy/?step=1';
		CE_Test_State::$terms['chapters']            = array(
			$this->makeTerm( 3, 'book-one', 'Book One' ),
			$this->makeTerm( 5, 'tom-jerry', "Tom & Jerry's" ),
		);
		foreach ( array( 'first_comic', 'last_comic', 'previous_comic', 'next_comic', 'first_comic_in_chapter', 'last_comic_in_chapter', 'previous_comic_in_chapter', 'next_comic_in_chapter' ) as $i => $name ) {
			CE_Test_State::$nav_links[ 'ceo_get_' . $name . '_permalink' ] = 'https://example.test/?p=' . ( 100 + $i );
		}
		CE_Test_State::$nav_links['ceo_get_previous_chapter'] = 'https://example.test/chapters/book-one';
		CE_Test_State::$nav_links['ceo_get_next_chapter']     = 'https://example.test/chapters/tom-jerry';
		$GLOBALS['post'] = $this->makePost( 7, 'The <em>Big</em> Day' );
	}

	public function testMarkupIsUnchangedByEscaping() {
		$this->onAComicWithNeighbours();
		$this->assertSameAsRecorded( 'navigation.html', $this->render( $this->everythingOn() ) );
	}

	public function testChapterLinksAndComicTitleGoThroughKses() {
		$this->onAComicWithNeighbours();
		$this->render( $this->everythingOn() );

		$this->assertContains( '<a href="https://example.test/chapters/book-one" rel="tag">Book One</a>, <a href="https://example.test/chapters/tom-jerry" rel="tag">Tom & Jerry\'s</a>', CE_Test_State::$kses_calls );
		$this->assertContains( CE_KSES_SENTINEL . 'The <em>Big</em> Day', CE_Test_State::$kses_calls );
	}

	public function testRendersTheLabelsFromTheFormDefaultsForAMissingTitle() {
		// Checkbox and label keys have always been saved together, so this takes a hand-built
		// instance: a checked link whose label key is missing shows the form's default label.
		$this->setGlobalPost( 7 );
		CE_Test_State::$nav_links['ceo_get_next_comic_permalink'] = 'https://example.test/?p=8';
		$out = $this->render( array( 'next' => true ) );

		$this->assertStringContainsString( '<a href="https://example.test/?p=8" class="navi comic-nav-next navi-next" title="Next &gt;">Next &gt;</a>', $out );
	}
}
