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

	public function testMarkupIsUnchangedByEscaping() {
		$this->assertSameAsRecorded( 'latest-comics.html', $this->render( array( 'title' => '', 'count' => 2 ) ) );
	}

	public function testWrappersTitleAndComicTitlesGoThroughKses() {
		$this->render( array( 'title' => '', 'count' => 2 ) );

		$args = self::widgetArgs();
		$this->assertContains( $args['before_widget'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['before_title'] . 'Latest Comics' . $args['after_title'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['after_widget'], CE_Test_State::$kses_calls );
		$this->assertContains( CE_KSES_SENTINEL . 'Fish &amp; <em>Chips</em>', CE_Test_State::$kses_calls );
	}

	public function testDefaultTitleIsEscaped() {
		CE_Test_State::$translations['Latest Comics'] = 'Derniers <b>';
		$out = $this->render( array( 'title' => '', 'count' => 2 ) );

		$this->assertStringContainsString( '<h2 class="widget-title">Derniers &lt;b&gt;</h2>', $out );
	}

	public function testUpdateWithNoFieldsSubmitted() {
		$widget = new ceo_latest_comics_widget();
		$out    = $widget->update( array(), array() );

		$this->assertSame( '', $out['title'] );
		$this->assertSame( 5, $out['count'] );
	}
}
