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

	public function testUpdateWithNoFieldsSubmitted() {
		$widget = new ceo_scheduled_comics_widget();
		$out    = $widget->update( array(), array() );

		$this->assertSame( '', $out['title'] );
	}
}
