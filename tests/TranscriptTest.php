<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * ceo_the_transcript() — functions/shortcodes.php
 *
 * Characterization tests. These assert behaviour that holds regardless of how the
 * transcript is escaped on output, so they stay green while the escaping work lands.
 * The escaping assertions themselves ship with the commit that adds the escaping.
 */
class TranscriptTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/shortcodes.php' );
		$this->setGlobalPost( 1 );
	}

	private function withTranscript( $value ) {
		$this->setPostMeta( 1, 'transcript', $value );
	}

	public function testReturnsNullWhenNoTranscriptStored() {
		$this->assertNull( ceo_the_transcript( 'raw' ) );
	}

	/**
	 * The guard is !empty(), so the string "0" is indistinguishable from no transcript at
	 * all. Pinned deliberately: it is surprising, it predates this work, and a future
	 * change to the guard should have to notice it.
	 */
	public function testTranscriptOfLiteralZeroIsTreatedAsAbsent() {
		$this->withTranscript( '0' );
		$this->assertNull( ceo_the_transcript( 'raw' ) );
	}

	/**
	 * The switch has no default arm, so an unrecognised display mode falls through and the
	 * function returns nothing. [transcript display=html] therefore renders silently empty.
	 */
	public function testUnknownDisplayModeReturnsNull() {
		$this->withTranscript( 'Panel one.' );
		$this->assertNull( ceo_the_transcript( 'html' ) );
	}

	public function testRawModeReturnsTheTranscript() {
		$this->withTranscript( "Panel one.\nPanel two." );
		$this->assertStringContainsString( 'Panel one.', ceo_the_transcript( 'raw' ) );
		$this->assertStringContainsString( 'Panel two.', ceo_the_transcript( 'raw' ) );
	}

	/**
	 * nl2br() is applied after whatever escaping is in force, so the <br /> must be live
	 * markup in the output rather than an escaped literal.
	 */
	public function testBrModeConvertsNewlinesToLiveMarkup() {
		$this->withTranscript( "one\ntwo" );
		$out = ceo_the_transcript( 'br' );
		$this->assertStringContainsString( '<br />', $out );
	}

	public function testStyledModeWrapsTranscriptInTheExpanderMarkup() {
		$this->withTranscript( 'Panel one.' );
		$out = ceo_the_transcript( 'styled' );
		$this->assertStringContainsString( 'transcript-border', $out );
		$this->assertStringContainsString( 'transcript-content', $out );
		$this->assertStringContainsString( 'Panel one.', $out );
	}

	/**
	 * Display must be stable: a transcript stored the way the plugin's save handler stores
	 * it -- through esc_textarea() -- must render back as exactly those bytes, so that what
	 * the author typed is what the reader sees, on this render and every later one.
	 *
	 * The case that matters is an author who typed a literal entity. esc_textarea() stores
	 * "&lt;b&gt;" as "&amp;lt;b&amp;gt;". An escape that declines to re-encode existing
	 * entities -- which is what esc_html() does, since it passes $double_encode = false --
	 * gives back "&lt;b&gt;" instead, one level short. Repeat that on each save and the
	 * author's literal text decays into a live <b> tag.
	 */
	#[DataProvider( 'authoredTextProvider' )]
	public function testRenderingATranscriptIsStableAcrossSaves( $typed ) {
		$stored = esc_textarea( $typed );
		$this->withTranscript( $stored );
		$this->assertSame(
			$stored,
			ceo_the_transcript( 'raw' ),
			'Rendering must return the stored bytes unchanged, or encoding is lost on each save'
		);
	}

	public static function authoredTextProvider() {
		return array(
			'markup the author wants shown as text' => array( '<b>bold</b>' ),
			'an entity the author typed literally'  => array( '&lt;b&gt;' ),
			'a bare ampersand'                      => array( 'Tom & Jerry' ),
			'an ampersand the author escaped'       => array( 'Tom &amp; Jerry' ),
			'quotes'                                => array( 'she said "hi"' ),
		);
	}
}
