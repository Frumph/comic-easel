<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * SQL injection via shortcode attributes — functions/shortcodes.php
 *
 * [comic-archive] and [cast-page] pass their attributes into hand-built SQL. Shortcode
 * attributes are authored content, so on a site with more than one contributor these are
 * untrusted input: anyone who can write a post can set them.
 *
 * The $wpdb spy records the SQL, so these assert on the query text with no database. That is
 * the right tool here regardless — the queries use MySQL-only date functions, so a SQLite test
 * database would not tell us the truth about them.
 */
class ShortcodeInjectionTest extends CE_TestCase {

	/** @var CE_WPDB_Spy */
	private $wpdb;

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/shortcodes.php' );
		$this->wpdb = $this->useWpdbSpy();
		$_GET['archive_year'] = '2020';
	}

	protected function tearDown(): void {
		unset( $_GET['archive_year'] );
		parent::tearDown();
	}

	/**
	 * ORDER BY cannot be a prepare() placeholder, so the direction has to be whitelisted.
	 * Anything that is not recognisably DESC must come out as the literal ASC.
	 */
	#[DataProvider( 'orderProvider' )]
	public function testOrderDirectionIsWhitelisted( $given, $expected ) {
		ceo_archive_list_by_year( false, $given, 5 );
		$sql = $this->wpdb->last();
		$this->assertStringContainsString( 'ORDER BY post_date ' . $expected, $sql );
	}

	public static function orderProvider() {
		return array(
			'lowercase desc'   => array( 'desc', 'DESC' ),
			'uppercase DESC'   => array( 'DESC', 'DESC' ),
			'lowercase asc'    => array( 'asc', 'ASC' ),
			'empty'            => array( '', 'ASC' ),
			'sql appended'     => array( 'ASC; DROP TABLE wp_posts', 'ASC' ),
			'comment injection'=> array( 'DESC--', 'ASC' ),
			'union attempt'    => array( 'ASC UNION SELECT user_pass FROM wp_users', 'ASC' ),
			'leading space'    => array( ' DESC', 'ASC' ),
		);
	}

	#[DataProvider( 'injectionProvider' )]
	public function testInjectedSqlNeverReachesTheYearQuery( $order, $chapter, $needle ) {
		ceo_archive_list_by_year( false, $order, $chapter );
		$this->assertFalse(
			$this->wpdb->sawSql( $needle ),
			'attacker-controlled text must not appear anywhere in the SQL'
		);
	}

	#[DataProvider( 'injectionProvider' )]
	public function testInjectedSqlNeverReachesTheAllYearsQuery( $order, $chapter, $needle ) {
		ceo_archive_list_by_all_years( false, $order, $chapter );
		$this->assertFalse( $this->wpdb->sawSql( $needle ) );
	}

	public static function injectionProvider() {
		return array(
			'union via chapter'   => array( 'ASC', '5 UNION SELECT user_pass FROM wp_users', 'user_pass' ),
			'or-true via chapter' => array( 'ASC', '5 OR 1=1', 'OR 1=1' ),
			'comment via chapter' => array( 'ASC', '5 -- ', '-- ' ),
			'drop via order'      => array( 'ASC; DROP TABLE wp_posts', 0, 'DROP TABLE' ),
			'sleep via order'     => array( 'ASC, SLEEP(5)', 0, 'SLEEP' ),
		);
	}

	#[DataProvider( 'chapterCastProvider' )]
	public function testChapterIsCastToIntegerInTheYearQuery( $given, $expected ) {
		ceo_archive_list_by_year( false, 'ASC', $given );
		$this->assertStringContainsString( 'term_id = ' . $expected, $this->wpdb->last() );
	}

	public static function chapterCastProvider() {
		return array(
			'plain integer'   => array( 5, '5' ),
			'numeric string'  => array( '5', '5' ),
			'injection'       => array( '5 OR 1=1', '5' ),
			'float'           => array( 1.9, '1' ),
		);
	}

	/**
	 * [cast-page chapter=N] — the same attribute reaching a different query.
	 */
	public function testCastPageChapterIsParameterised() {
		ceo_get_character_list( '5 UNION SELECT user_pass FROM wp_users' );
		$sql = $this->wpdb->last();
		$this->assertStringContainsString( 'terms1.term_id = 5', $sql );
		$this->assertStringNotContainsString( 'user_pass', $sql );
	}

	public function testCastPageChapterAcceptsALegitimateId() {
		ceo_get_character_list( 42 );
		$this->assertStringContainsString( 'terms1.term_id = 42', $this->wpdb->last() );
	}
}
