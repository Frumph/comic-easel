<?php

/**
 * Proves the harness itself works: that plugin files can be loaded against the stubs, and
 * that the three stubs the other tests depend on behave the way WordPress does.
 *
 * If this file fails, treat every other assertion in the suite as unreliable.
 */
class HarnessTest extends CE_TestCase {

	public function testPluginFilesLoadAgainstStubs() {
		self::loadPluginFile( 'functions/shortcodes.php' );
		self::loadPluginFile( 'functions/displaycomic.php' );
		self::loadPluginFile( 'widgets/bf_adwidget.php' );
		self::loadPluginFile( 'widgets/navigation.php' );

		$this->assertTrue( function_exists( 'ceo_the_transcript' ) );
		$this->assertTrue( function_exists( 'ceo_get_character_list' ) );
		$this->assertTrue( class_exists( 'ceo_bf_adwidget' ) );
		$this->assertTrue( class_exists( 'ceo_comic_navigation_widget' ) );
	}

	/**
	 * esc_html()/esc_attr() must NOT re-encode existing entities; esc_textarea() must.
	 * Several tests distinguish correct from incorrect behaviour purely on this, so if the
	 * stubs get it wrong those tests silently stop meaning anything.
	 */
	public function testEscapingStubsMirrorWordPressDoubleEncodeSemantics() {
		$this->assertSame( '&lt;b&gt;', esc_html( '&lt;b&gt;' ), 'esc_html must not double-encode' );
		$this->assertSame( '&lt;b&gt;', esc_attr( '&lt;b&gt;' ), 'esc_attr must not double-encode' );
		$this->assertSame( '&amp;lt;b&amp;gt;', esc_textarea( '&lt;b&gt;' ), 'esc_textarea must double-encode' );

		$this->assertSame( '&lt;b&gt;', esc_html( '<b>' ) );
		$this->assertSame( '&quot;x&quot;', esc_attr( '"x"' ) );
	}

	public function testKsesStubRecordsRatherThanFilters() {
		$out = wp_kses_post( '<script>x</script>' );
		$this->assertSame( CE_KSES_SENTINEL . '<script>x</script>', $out );
		$this->assertSame( array( '<script>x</script>' ), CE_Test_State::$kses_calls );
	}

	public function testFilterStubIsOverridable() {
		$this->assertSame( 'default', apply_filters( 'some_tag', 'default' ) );
		CE_Test_State::$filters['some_tag'] = 'overridden';
		$this->assertSame( 'overridden', apply_filters( 'some_tag', 'default' ) );
	}

	public function testTranslationStubIsOverridable() {
		$this->assertSame( 'Original', __( 'Original', 'comiceasel' ) );
		CE_Test_State::$translations['Original'] = 'Origineel';
		$this->assertSame( 'Origineel', __( 'Original', 'comiceasel' ) );
	}

	public function testEscUrlStubRejectsDisallowedSchemes() {
		$this->assertSame( '', esc_url( 'javascript:alert(1)' ) );
		$this->assertSame( '', esc_url_raw( 'javascript:alert(1)' ) );
		$this->assertSame( 'https://example.test/x', esc_url_raw( 'https://example.test/x' ) );
	}

	public function testWpdbSpyRecordsPreparedSql() {
		$wpdb = $this->useWpdbSpy();
		$sql  = $wpdb->prepare( 'SELECT * FROM t WHERE id = %d AND name = %s', '7 OR 1=1', "o'brien" );
		$this->assertSame( "SELECT * FROM t WHERE id = 7 AND name = 'o\\'brien'", $sql );
		$this->assertTrue( $wpdb->sawSql( 'id = 7' ) );
	}

	public function testStateResetsBetweenTests() {
		$this->assertSame( array(), CE_Test_State::$kses_calls );
		$this->assertSame( 'UTF-8', get_option( 'blog_charset' ) );
	}
}
