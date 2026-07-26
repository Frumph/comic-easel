<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * ceo_escape_stored_text() — functions/library.php
 *
 * The shared escaper for comic meta rendered as HTML text. Two things about it are easy to
 * get wrong and neither is visible on inspection, which is why they are pinned here.
 */
class EscapeStoredTextTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
	}

	/**
	 * A single invalid UTF-8 byte must not blank the value.
	 *
	 * From PHP 8.1 the default flag set for htmlspecialchars() is
	 * ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401. Passing ENT_QUOTES alone replaces that
	 * default instead of adding to it, dropping ENT_SUBSTITUTE — and without it a malformed
	 * byte makes htmlspecialchars() return an empty string. A transcript with one stray byte
	 * would render as nothing at all.
	 */
	public function testInvalidUtf8DoesNotBlankTheValue() {
		$out = ceo_escape_stored_text( "Panel one\x80 and two" );
		$this->assertNotSame( '', $out );
		$this->assertStringContainsString( 'Panel one', $out );
		$this->assertStringContainsString( 'and two', $out );
	}

	/** An empty or unrecognised blog_charset must not blank the value either. */
	#[DataProvider( 'badCharsetProvider' )]
	public function testUnusableBlogCharsetDoesNotBlankTheValue( $charset ) {
		CE_Test_State::$options['blog_charset'] = $charset;
		$this->assertStringContainsString( 'Panel one', ceo_escape_stored_text( 'Panel one' ) );
	}

	public static function badCharsetProvider() {
		return array(
			'empty'  => array( '' ),
			'false'  => array( false ),
			'null'   => array( null ),
		);
	}

	/**
	 * Rendering must be stable across saves: a value stored the way the meta boxes store it
	 * must come back as exactly those bytes. See the header of functions/library.php for why
	 * esc_html() cannot be used here.
	 */
	#[DataProvider( 'authoredTextProvider' )]
	public function testRenderingIsStableAcrossSaves( $typed ) {
		$stored = esc_textarea( $typed );
		$this->assertSame( $stored, ceo_escape_stored_text( $stored ) );
	}

	public static function authoredTextProvider() {
		return array(
			'markup shown as text'            => array( '<b>bold</b>' ),
			'an entity typed literally'       => array( '&lt;b&gt;' ),
			'a bare ampersand'                => array( 'Tom & Jerry' ),
			'an ampersand already escaped'    => array( 'Tom &amp; Jerry' ),
			'quotes'                          => array( 'she said "hi"' ),
			'an apostrophe'                   => array( "it's fine" ),
		);
	}

	/** Raw markup written past the meta boxes must come out inert. */
	public function testRawMarkupIsNeutralised() {
		$out = ceo_escape_stored_text( '<script>alert(1)</script>' );
		$this->assertStringNotContainsString( '<script', $out );
		$this->assertSame( '&lt;script&gt;alert(1)&lt;/script&gt;', $out );
	}

	public function testAttributeBreakingCharactersAreEscaped() {
		$this->assertSame( '&quot;', ceo_escape_stored_text( '"' ) );
		$this->assertSame( '&#039;', ceo_escape_stored_text( "'" ) );
	}

	#[DataProvider( 'nonStringProvider' )]
	public function testNonStringInputIsHandled( $input, $expected ) {
		$this->assertSame( $expected, ceo_escape_stored_text( $input ) );
	}

	public static function nonStringProvider() {
		return array(
			array( null, '' ),
			array( false, '' ),
			array( 123, '123' ),
		);
	}
}
