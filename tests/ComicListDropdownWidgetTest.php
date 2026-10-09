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

	public function testUpdateWithNoFieldsSubmitted() {
		$widget = new ceo_comic_list_dropdown_widget();
		$out    = $widget->update( array(), array() );

		$this->assertSame( '', $out['title'] );
		$this->assertSame( '', $out['exclude'] );
	}
}
