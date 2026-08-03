<?php

/**
 * Hand-written database queries outside the shortcode archive code.
 *
 * The spy records the SQL after prepare() has substituted placeholders. These tests focus
 * on the values that can vary at runtime: post IDs, taxonomy names, term IDs, dates, and
 * timezone offsets. WordPress-owned table names and deliberately filtered SQL fragments are
 * structural query parts and are covered separately by review and runtime checks.
 */
class DatabaseQueryTest extends CE_TestCase {

	/** @var CE_WPDB_Spy */
	private $wpdb;

	protected function setUp(): void {
		parent::setUp();
		$this->wpdb = $this->useWpdbSpy();
	}

	public function testLastPostModifiedUsesAnIntegerTimezoneOffset() {
		self::loadPluginFile( 'functions/filters.php' );

		ceo_lastpostmodified();

		$this->assertMatchesRegularExpression( '/INTERVAL -?\d+ SECOND/', $this->wpdb->last() );
		$this->assertStringNotContainsString( "INTERVAL '", $this->wpdb->last() );
	}

	public function testRelatedComicQueryAcceptsOnlyIntegerTermAndPostIds() {
		self::loadPluginFile( 'functions/injections.php' );
		$post             = $this->setGlobalPost( 42 );
		$post->post_type  = 'comic';
		$malicious_term   = new stdClass();
		$malicious_term->term_id = '7) UNION SELECT user_pass FROM wp_users --';
		$valid_term       = new stdClass();
		$valid_term->term_id = 9;
		CE_Test_State::$terms['characters'] = array( $malicious_term, $valid_term );

		ob_start();
		ceo_display_related_comics();
		ob_end_clean();

		$sql = $this->wpdb->last();
		$this->assertStringContainsString( 'tt.term_id IN (7,9)', $sql );
		$this->assertStringContainsString( 'p.ID != 42', $sql );
		$this->assertStringNotContainsString( 'user_pass', $sql );
	}

	public function testCalendarQueryPreparesTheTaxonomyName() {
		self::loadPluginFile( 'widgets/comic-calendar.php' );
		$this->setCalendarGlobals();

		ceo_get_calendar( true, false, "comic' OR 1=1 --" );

		$this->assertTrue( $this->wpdb->sawSql( "post_type = 'comic\\' OR 1=1 --'" ) );
		$this->assertFalse( $this->wpdb->sawSql( "post_type = 'comic' OR 1=1" ) );
	}

	public function testCalendarQueriesKeepLegitimateCustomPostTypeKeys() {
		self::loadPluginFile( 'widgets/comic-calendar.php' );
		$this->setCalendarGlobals();

		ceo_get_calendar( true, false, 'my_comic-type' );

		$this->assertTrue( $this->wpdb->sawSql( "post_type = 'my_comic-type'" ) );
	}

	public function testCalendarQueriesPreserveNoncanonicalLegacyPostTypeKeys() {
		self::loadPluginFile( 'widgets/comic-calendar.php' );
		$this->setCalendarGlobals();

		ceo_get_calendar( true, false, 'Legacy.Comic_Type' );

		$this->assertTrue( $this->wpdb->sawSql( "post_type = 'Legacy.Comic_Type'" ) );
	}

	public function testCalendarQueryFallsBackSafelyForArrayTaxonomyInput() {
		self::loadPluginFile( 'widgets/comic-calendar.php' );
		$this->setCalendarGlobals();

		ceo_get_calendar( true, false, array( 'comic' ) );

		$this->assertTrue( $this->wpdb->sawSql( "post_type = 'post'" ) );
		$this->assertFalse( $this->wpdb->sawSql( 'Array' ) );
	}

	private function setCalendarGlobals() {
		$GLOBALS['m']        = '';
		$GLOBALS['monthnum'] = 1;
		$GLOBALS['year']     = 2020;
		$GLOBALS['posts']    = array( (object) array( 'ID' => 1 ) );
		$GLOBALS['wp_locale'] = new class() {
			public function get_month( $month ) {
				return 'January';
			}
			public function get_month_abbrev( $month ) {
				return 'Jan';
			}
			public function get_weekday( $day ) {
				return 'Day' . $day;
			}
			public function get_weekday_initial( $day ) {
				return substr( $day, 0, 1 );
			}
			public function get_weekday_abbrev( $day ) {
				return $day;
			}
		};
		$_SERVER['HTTP_USER_AGENT'] = 'Test Browser';
	}
}
