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

	public function testRendersTheLabelsFromTheFormDefaultsForAMissingTitle() {
		// Checkbox and label keys have always been saved together, so this takes a hand-built
		// instance: a checked link whose label key is missing shows the form's default label.
		$this->setGlobalPost( 7 );
		CE_Test_State::$nav_links['ceo_get_next_comic_permalink'] = 'https://example.test/?p=8';
		$out = $this->render( array( 'next' => true ) );

		$this->assertStringContainsString( '<a href="https://example.test/?p=8" class="navi comic-nav-next navi-next" title="Next &gt;">Next &gt;</a>', $out );
	}
}
