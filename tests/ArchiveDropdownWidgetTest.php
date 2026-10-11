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
		// A Legacy Widget block that was never saved renders with no settings at all. Empty
		// chapters were already hidden by default; the comic count stays off, as it always rendered.
		$this->setGlobalPost( 7 );
		$out = $this->render( array() );

		$this->assertStringContainsString( "<select  name='chapters' id='chapters' class='postform ceo-jump-menu'>", $out );
		$this->assertStringNotContainsString( '(4)', $out, 'the count is off by default' );
	}

	public function testRendersAnInstanceSavedBeforeTheCountAndArchiveOptionsExisted() {
		// The shape a widget saved before May 2014 still has.
		$this->setGlobalPost( 7 );
		$out = $this->render( array( 'title' => 'Chapters', 'exclude' => '', 'unhide' => true ) );

		$this->assertStringNotContainsString( '(4)', $out, 'no count, as before the key existed' );
		$this->assertStringContainsString( '<option class="level-0" value=""', $out, 'jumping to the first comic, of which the stub has none' );
	}

	public function testListRenderingWithNoCurrentPost() {
		// A 404 page has no post to mark the current chapter from.
		$GLOBALS['post'] = null;
		$out = $this->render( array( 'render_as_list' => 1, 'jumptoarchive' => 1, 'showcount' => 0 ) );

		$this->assertStringContainsString( '<li class="level-0 cat-item cat-item-3"><a href="https://example.test/chapters/book-one">', $out );
		$this->assertStringNotContainsString( 'current-cat', $out );
	}

	private function dropdownInstance() {
		return array( 'title' => 'Chapters', 'exclude' => '', 'hide' => 1, 'showcount' => 1, 'jumptoarchive' => 1, 'render_as_list' => 0 );
	}

	public function testDropdownMarkupIsUnchangedByEscaping() {
		$this->setGlobalPost( 7 );
		$this->assertSameAsRecorded( 'archive-dropdown-select.html', $this->render( $this->dropdownInstance() ) );
	}

	public function testListMarkupIsUnchangedByEscaping() {
		$this->setGlobalPost( 7 );
		$instance = array_merge( $this->dropdownInstance(), array( 'render_as_list' => 1 ) );
		$this->assertSameAsRecorded( 'archive-dropdown-list.html', $this->render( $instance ) );
	}

	public function testThemeWrappersAndTitleGoThroughKses() {
		$this->setGlobalPost( 7 );
		$this->render( $this->dropdownInstance() );

		$args = self::widgetArgs();
		$this->assertContains( $args['before_widget'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['before_title'] . 'Chapters' . $args['after_title'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['after_widget'], CE_Test_State::$kses_calls );
	}

	public function testDropdownIsFilteredByAnAllowlistThatKeepsTheMenuWorking() {
		// wp_kses_post() would strip the <select> and its options, so the menu is filtered
		// against its own allowlist, which keeps the class js/jump-menu.js navigates by.
		$this->setGlobalPost( 7 );
		$out = $this->render( $this->dropdownInstance() );

		$this->assertCount( 1, CE_Test_State::$kses_allowlist_calls );
		list( $markup, $allowed ) = CE_Test_State::$kses_allowlist_calls[0];
		$this->assertStringContainsString( $markup, $out, 'the filtered markup is what was printed' );
		$this->assertStringContainsString( "class='postform ceo-jump-menu'", $markup );
		$this->assertAllowedByKses( $markup, $allowed );
	}

	public function testDropdownNavigatesThroughTheEnqueuedScriptNotAnInlineHandler() {
		CE_Test_State::$pluginfo['plugin_url'] = 'https://example.test/wp-content/plugins/comic-easel/';
		CE_Test_State::$pluginfo['version']    = '1.19';
		$this->setGlobalPost( 7 );
		$out = $this->render( $this->dropdownInstance() );

		$this->assertDoesNotMatchRegularExpression( '#\son[a-z]+=#i', $out );
		$this->assertSame(
			array( 'src' => 'https://example.test/wp-content/plugins/comic-easel/js/jump-menu.js', 'deps' => array(), 'ver' => '1.19', 'args' => true ),
			CE_Test_State::$scripts['ceo-jump-menu']
		);
	}

	public function testListRenderingLoadsNoJumpMenuScript() {
		$this->setGlobalPost( 7 );
		$this->render( array_merge( $this->dropdownInstance(), array( 'render_as_list' => 1 ) ) );

		$this->assertArrayNotHasKey( 'ceo-jump-menu', CE_Test_State::$scripts );
	}

	public function testListIsFilteredAsPostMarkup() {
		// Chapter names in the list have already been through wp_kses_post(), so the list as a
		// whole is post-style markup and gets the same treatment.
		$this->setGlobalPost( 7 );
		$out = $this->render( array_merge( $this->dropdownInstance(), array( 'render_as_list' => 1 ) ) );

		$this->assertSame( array(), CE_Test_State::$kses_allowlist_calls );
		$this->assertStringContainsString( CE_KSES_SENTINEL . '<ul class="chapter-select">', $out );
	}

	public function testSelectPromptIsEscaped() {
		CE_Test_State::$translations['Select'] = 'Choisir <b>un</b>';
		$this->setGlobalPost( 7 );
		$out = $this->render( $this->dropdownInstance() );

		$this->assertStringContainsString( "<option value='0' selected='selected'>Choisir &lt;b&gt;un&lt;/b&gt; Chapters</option>", $out );
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
