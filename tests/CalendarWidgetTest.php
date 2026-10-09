<?php

/**
 * Answers the calendar's queries: comics last December and next February, and on the 15th
 * and 20th of the month shown.
 */
class CE_Calendar_WPDB_Spy extends CE_WPDB_Spy {
	public function get_row( $query = null, $output = null, $y = 0 ) {
		parent::get_row( $query );
		return false !== strpos( $query, 'DESC' )
			? (object) array( 'month' => 12, 'year' => 2019 )
			: (object) array( 'month' => 2, 'year' => 2020 );
	}

	public function get_results( $query = null, $output = null ) {
		parent::get_results( $query );
		if ( ARRAY_N === $output ) {
			return array( array( 15 ), array( 20 ) );
		}
		return array( (object) array( 'ID' => 21, 'post_title' => 'A post', 'dom' => 15 ) );
	}
}

/**
 * Comic Calendar widget — widgets/comic-calendar.php
 */
class CalendarWidgetTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'widgets/comic-calendar.php' );
		$GLOBALS['wpdb']     = new CE_Calendar_WPDB_Spy();
		$GLOBALS['m']        = '';
		$GLOBALS['monthnum'] = 1;
		$GLOBALS['year']     = 2020;
		$GLOBALS['posts']    = array( (object) array( 'ID' => 1 ) );
		$GLOBALS['wp_locale'] = new class() {
			private $months   = array( 1 => 'January', 2 => 'February', 12 => 'December' );
			private $weekdays = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
			public function get_month( $month ) {
				return $this->months[ (int) $month ];
			}
			public function get_month_abbrev( $month_name ) {
				return substr( $month_name, 0, 3 );
			}
			public function get_weekday( $day ) {
				return $this->weekdays[ $day ];
			}
			public function get_weekday_initial( $day_name ) {
				return substr( $day_name, 0, 1 );
			}
			public function get_weekday_abbrev( $day_name ) {
				return substr( $day_name, 0, 3 );
			}
		};
		CE_Test_State::$options['permalink_structure'] = '/%postname%/';
		$_SERVER['HTTP_USER_AGENT'] = 'Test Browser';
	}

	private function render( $instance ) {
		$widget = new ceo_calendar_widget();
		return $this->capture( function () use ( $widget, $instance ) {
			$widget->widget( self::widgetArgs(), $instance );
		} );
	}

	private function withDownloads() {
		return array(
			'thumbnail' => 'https://example.test/wallpaper-thumb.png',
			'link'      => 'https://example.test/wallpapers/',
			'small'     => 'https://example.test/wallpaper-small.png',
			'large'     => 'https://example.test/wallpaper-large.png',
		);
	}

	public function testMarkupIsUnchangedByEscaping() {
		$this->assertSameAsRecorded( 'calendar.html', $this->render( $this->withDownloads() ) );
	}

	public function testWrappersAndCalendarGoThroughKses() {
		$this->render( $this->withDownloads() );

		$args = self::widgetArgs();
		$this->assertContains( $args['before_widget'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['after_widget'], CE_Test_State::$kses_calls );
		$calendar = array_filter( CE_Test_State::$kses_calls, function ( $call ) {
			return 0 === strpos( $call, '<table id="wp-calendar"' );
		} );
		$this->assertCount( 1, $calendar );
	}

	public function testReturnedCalendarIsUnfiltered() {
		// Returning callers print it themselves, and escape it there.
		$calendar = ceo_get_calendar( true, false, 'comic' );

		$this->assertStringStartsWith( '<table id="wp-calendar"', $calendar );
		$this->assertSame( array(), CE_Test_State::$kses_calls );
	}

	public function testMonthLinksAreEscapedWithoutPrettyPermalinks() {
		// Without pretty permalinks the post type is appended as a second query argument.
		CE_Test_State::$options['permalink_structure'] = '';
		$calendar = ceo_get_calendar( true, false, 'comic' );

		$this->assertStringContainsString( '<a href="https://example.test/2019/12/&#038;post_type=comic" title="View posts for December 2019">&laquo; Dec</a>', $calendar );
		$this->assertStringContainsString( '<a href="https://example.test/2020/1/15/&#038;post_type=comic">15</a>', $calendar );
	}

	public function testFormMarkupIsUnchangedByEscaping() {
		$widget = new ceo_calendar_widget();
		$out    = $this->capture( function () use ( $widget ) {
			$widget->form( $this->withDownloads() );
		} );

		$this->assertSameAsRecorded( 'calendar-form.html', $out );
		$this->assertContains( '<hr />', CE_Test_State::$kses_calls );
	}
}
