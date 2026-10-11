<?php

/**
 * Comic List Dropdown widget — widgets/comiclist-dropdown.php
 */
class ComicListDropdownWidgetTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'widgets/comiclist-dropdown.php' );
		CE_Test_State::$terms['chapters'] = array( $this->makeTerm( 3, 'book-one', 'Book One' ) );
		CE_Test_State::$found_posts       = array(
			$this->makePost( 21, 'Page One' ),
			$this->makePost( 22, 'Fish & "Chips"' ),
		);
	}

	private function render( $instance ) {
		$widget = new ceo_comic_list_dropdown_widget();
		return $this->capture( function () use ( $widget, $instance ) {
			$widget->widget( self::widgetArgs(), $instance );
		} );
	}

	public function testRendersAnEmptyInstance() {
		// A Legacy Widget block that was never saved renders with no settings at all.
		$this->setGlobalPost( 21 );
		$out = $this->render( array() );

		$this->assertStringContainsString( '<option class="level-0" value="https://example.test/?p=22">Fish &amp; &quot;Chips&quot;</option>', $out );
		$this->assertStringNotContainsString( 'widget-title', $out );
	}

	public function testMarkupIsUnchangedByEscaping() {
		$this->setGlobalPost( 21 );
		$this->assertSameAsRecorded( 'comiclist-dropdown.html', $this->render( array( 'title' => 'Jump', 'exclude' => '' ) ) );
	}

	public function testThemeWrappersAndTitleGoThroughKses() {
		$this->setGlobalPost( 21 );
		$this->render( array( 'title' => 'Jump', 'exclude' => '' ) );

		$args = self::widgetArgs();
		$this->assertContains( $args['before_widget'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['before_title'] . 'Jump' . $args['after_title'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['after_widget'], CE_Test_State::$kses_calls );
	}

	public function testMenuIsFilteredByAnAllowlistThatKeepsItWorking() {
		$this->setGlobalPost( 21 );
		$out = $this->render( array( 'title' => '', 'exclude' => '' ) );

		$this->assertCount( 1, CE_Test_State::$kses_allowlist_calls );
		list( $markup, $allowed ) = CE_Test_State::$kses_allowlist_calls[0];
		$this->assertStringContainsString( $markup, $out );
		$this->assertStringContainsString( '<select class="ceo-jump-menu">', $markup );
		$this->assertAllowedByKses( $markup, $allowed );
	}

	public function testMenuNavigatesThroughTheEnqueuedScriptNotAnInlineHandler() {
		CE_Test_State::$pluginfo['plugin_url'] = 'https://example.test/wp-content/plugins/comic-easel/';
		CE_Test_State::$pluginfo['version']    = '1.19';
		$this->setGlobalPost( 21 );
		$out = $this->render( array( 'title' => '', 'exclude' => '' ) );

		$this->assertDoesNotMatchRegularExpression( '#\son[a-z]+=#i', $out );
		$this->assertSame(
			array( 'src' => 'https://example.test/wp-content/plugins/comic-easel/js/jump-menu.js', 'deps' => array(), 'ver' => '1.19', 'args' => true ),
			CE_Test_State::$scripts['ceo-jump-menu']
		);
	}

	public function testReturnedMenuIsUnfiltered() {
		// Returning callers print it themselves, and escape it there.
		$this->setGlobalPost( 21 );
		$menu = ceo_list_jump_to_comic( '', true );

		$this->assertStringStartsWith( '<form method="get" class="comic-list-dropdown-form">', $menu );
		$this->assertSame( array(), CE_Test_State::$kses_allowlist_calls );
	}

	/** Returning the menu releases what ceo_protect() saved, as printing it does. */
	public function testReturningTheMenuReleasesTheSavedPost() {
		$post = $this->setGlobalPost( 21 );
		ceo_list_jump_to_comic( '', true );

		$this->assertSame( $post, $GLOBALS['post'] );
		$this->assertNull( $GLOBALS['ceo_saved_post'] );
	}

	public function testJumpToPromptIsEscaped() {
		CE_Test_State::$translations['Jump To'] = 'Aller à <b>';
		$this->setGlobalPost( 21 );
		$out = $this->render( array( 'title' => '', 'exclude' => '' ) );

		$this->assertStringContainsString( '<option class="level-select" value="">Aller à &lt;b&gt;</option>', $out );
	}

	public function testUpdateWithNoFieldsSubmitted() {
		$widget = new ceo_comic_list_dropdown_widget();
		$out    = $widget->update( array(), array() );

		$this->assertSame( '', $out['title'] );
		$this->assertSame( '', $out['exclude'] );
	}
}
