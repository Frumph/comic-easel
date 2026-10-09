<?php

/**
 * Scheduled Posts widget — widgets/scheduledcomics.php
 */
class ScheduledComicsWidgetTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'widgets/scheduledcomics.php' );
	}

	private function render( $instance ) {
		$widget = new ceo_scheduled_comics_widget();
		return $this->capture( function () use ( $widget, $instance ) {
			$widget->widget( self::widgetArgs(), $instance );
		} );
	}

	public function testMarkupIsUnchangedByEscaping() {
		CE_Test_State::$found_posts = array(
			$this->makePost( 31, 'Next <em>week</em>', array( 'post_status' => 'future', 'post_date' => '2020-02-01 09:00:00' ) ),
			$this->makePost( 32, 'Fish &amp; Chips', array( 'post_status' => 'future', 'post_date' => '2020-02-08 09:00:00' ) ),
		);
		$this->assertSameAsRecorded( 'scheduled-comics.html', $this->render( array( 'title' => 'Coming up' ) ) );
	}

	public function testNothingScheduledMarkupIsUnchangedByEscaping() {
		$this->assertSameAsRecorded( 'scheduled-comics-none.html', $this->render( array() ) );
	}

	public function testWrappersAndTitleGoThroughKses() {
		$this->render( array( 'title' => 'Coming up' ) );

		$args = self::widgetArgs();
		$this->assertContains( $args['before_widget'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['before_title'] . 'Coming up' . $args['after_title'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['after_widget'], CE_Test_State::$kses_calls );
	}

	public function testDefaultTitleIsEscaped() {
		CE_Test_State::$translations['Scheduled Comics'] = 'Prévus <b>';
		$out = $this->render( array() );

		$this->assertStringContainsString( '<h2 class="widget-title">Prévus &lt;b&gt;</h2>', $out );
	}

	public function testUpdateWithNoFieldsSubmitted() {
		$widget = new ceo_scheduled_comics_widget();
		$out    = $widget->update( array(), array() );

		$this->assertSame( '', $out['title'] );
	}
}
