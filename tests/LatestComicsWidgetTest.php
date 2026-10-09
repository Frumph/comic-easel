<?php

/**
 * Latest Comics widget — widgets/recentcomics.php
 */
class LatestComicsWidgetTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'widgets/recentcomics.php' );
		CE_Test_State::$found_posts = array(
			$this->makePost( 21, 'Page One' ),
			$this->makePost( 22, 'Fish &amp; <em>Chips</em>' ),
		);
		WP_Query::$last_query = null;
	}

	private function render( $instance ) {
		$widget = new ceo_latest_comics_widget();
		return $this->capture( function () use ( $widget, $instance ) {
			$widget->widget( self::widgetArgs(), $instance );
		} );
	}

	public function testRendersAnEmptyInstance() {
		// A Legacy Widget block that was never saved renders with no settings at all. Its count
		// used to reach get_posts() as 0, which get_posts() replaces with its own default of 5.
		$out = $this->render( array() );

		$this->assertSame( 5, WP_Query::$last_query['showposts'] );
		$this->assertStringContainsString( '<h2 class="widget-title">Latest Comics</h2>', self::withoutKsesSentinel( $out ) );
	}

	public function testUpdateWithNoFieldsSubmitted() {
		$widget = new ceo_latest_comics_widget();
		$out    = $widget->update( array(), array() );

		$this->assertSame( '', $out['title'] );
		$this->assertSame( 5, $out['count'] );
	}
}
