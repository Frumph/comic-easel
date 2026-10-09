<?php

/**
 * Comic Chapters widget — widgets/archive-dropdown.php
 *
 * Like every widget, its saved settings only gain a key when the admin form is saved, and an
 * unchecked checkbox is missing from the submitted form altogether. phpunit.xml sets
 * failOnWarning, so an undefined-key read fails these tests outright.
 */
class ArchiveDropdownWidgetTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'widgets/archive-dropdown.php' );
		CE_Test_State::$pluginfo['chapter_type_slug_name'] = 'chapters';
		CE_Test_State::$terms['chapters'] = array(
			$this->makeTerm( 3, 'book-one', 'Book One', 'chapters', array( 'count' => 4 ) ),
			$this->makeTerm( 5, 'tom-jerry', "Tom & Jerry's", 'chapters', array( 'count' => 2 ) ),
		);
	}

	private function render( $instance ) {
		$widget = new ceo_comic_archive_dropdown_widget();
		return $this->capture( function () use ( $widget, $instance ) {
			$widget->widget( self::widgetArgs(), $instance );
		} );
	}

	public function testRendersAnEmptyInstanceWithTheFormDefaults() {
		// A Legacy Widget block that was never saved renders with no settings at all. The form
		// shows such a widget with "Hide empty chapters" and the comic count both ticked, and it
		// now renders that way too.
		$this->setGlobalPost( 7 );
		$out = $this->render( array() );

		$this->assertStringContainsString( "<select  name='chapters' id='chapters' class='postform' onchange=", $out );
		$this->assertStringContainsString( 'Book One&nbsp;&nbsp;(4)', $out, 'the count is on by default' );
	}

	public function testRendersAnInstanceSavedBeforeTheCountAndArchiveOptionsExisted() {
		// The shape a widget saved before May 2014 still has.
		$this->setGlobalPost( 7 );
		$out = $this->render( array( 'title' => 'Chapters', 'exclude' => '', 'unhide' => true ) );

		$this->assertStringContainsString( 'Book One&nbsp;&nbsp;(4)', $out );
		$this->assertStringContainsString( '<option class="level-0" value=""', $out, 'jumping to the first comic, of which the stub has none' );
	}

	public function testListRenderingWithNoCurrentPost() {
		// A 404 page has no post to mark the current chapter from.
		$GLOBALS['post'] = null;
		$out = $this->render( array( 'render_as_list' => 1, 'jumptoarchive' => 1, 'showcount' => 0 ) );

		$this->assertStringContainsString( '<li class="level-0 cat-item cat-item-3"><a href="https://example.test/chapters/book-one">', $out );
		$this->assertStringNotContainsString( 'current-cat', $out );
	}

	public function testUpdateStoresUncheckedBoxesAsOff() {
		// Browsers omit unchecked checkboxes from the form post, so only the text fields arrive.
		// Off is what they mean here, even for the boxes a new widget starts with ticked.
		$widget = new ceo_comic_archive_dropdown_widget();
		$out    = $widget->update( array( 'title' => 'Chapters', 'exclude' => '4,5' ), array() );

		foreach ( array( 'hide', 'showcount', 'jumptoarchive', 'render_as_list' ) as $key ) {
			$this->assertSame( 0, $out[ $key ], $key );
		}
		$this->assertSame( 'Chapters', $out['title'] );
		$this->assertSame( '4,5', $out['exclude'] );
	}

	public function testUpdateStoresCheckedBoxesAsOn() {
		$widget = new ceo_comic_archive_dropdown_widget();
		$out    = $widget->update( array( 'title' => '', 'exclude' => '', 'hide' => '1', 'render_as_list' => '1' ), array() );

		$this->assertSame( 1, $out['hide'] );
		$this->assertSame( 1, $out['render_as_list'] );
		$this->assertSame( 0, $out['showcount'] );
	}
}
