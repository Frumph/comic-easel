<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * ceo_meta_for_editor() — functions/admin-meta.php
 *
 * Normalises a stored comic meta value before it is re-displayed in the post editor. Comic
 * meta arrives in two shapes -- entity-encoded when written through the plugin's meta boxes,
 * which escape on save, and raw when written through WordPress's Custom Fields panel, which
 * does not. Decoding first is what makes a single escape at the output sink correct for both.
 */
class MetaForEditorTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/admin-meta.php' );
	}

	/**
	 * The property the meta boxes depend on: a value stored the way the save handler stores
	 * it, then normalised and re-escaped for a textarea, must come back byte-identical. If
	 * this fails, every existing comic shows mangled text in the editor.
	 */
	#[DataProvider( 'authoredTextProvider' )]
	public function testTextareaRoundTripIsLossless( $typed ) {
		$stored = esc_textarea( $typed );
		$this->assertSame( $stored, esc_textarea( ceo_meta_for_editor( $stored ) ) );
	}

	/** And the value the author actually typed is what they see again. */
	#[DataProvider( 'authoredTextProvider' )]
	public function testTheAuthorSeesWhatTheyTyped( $typed ) {
		$this->assertSame( $typed, ceo_meta_for_editor( esc_textarea( $typed ) ) );
	}

	public static function authoredTextProvider() {
		return array(
			'markup'                       => array( '<b>bold</b>' ),
			'an entity typed literally'    => array( '&lt;b&gt;' ),
			'a bare ampersand'             => array( 'Tom & Jerry' ),
			'quotes'                       => array( 'she said "hi"' ),
			'an apostrophe'                => array( "it's fine" ),
			'a url with a query string'    => array( 'https://x.test/?a=1&b=2' ),
		);
	}

	/**
	 * A raw value written past the meta boxes must not survive as live markup once the sink
	 * escapes it. This is the Custom Fields path.
	 */
	public function testRawMarkupFromCustomFieldsBecomesInert() {
		$raw = '</textarea><script>alert(1)</script>';
		$out = esc_textarea( ceo_meta_for_editor( $raw ) );
		$this->assertStringNotContainsString( '</textarea>', $out );
		$this->assertStringNotContainsString( '<script', $out );
	}

	public function testAttributeBreakingValueBecomesInert() {
		$out = esc_attr( ceo_meta_for_editor( '" autofocus onfocus="alert(1)' ) );
		$this->assertStringNotContainsString( '"', str_replace( '&quot;', '', $out ) );
	}

	#[DataProvider( 'decodingProvider' )]
	public function testKnownEntitiesAreDecodedOnce( $stored, $expected ) {
		$this->assertSame( $expected, ceo_meta_for_editor( $stored ) );
	}

	public static function decodingProvider() {
		return array(
			array( '&lt;b&gt;', '<b>' ),
			array( '&amp;amp;', '&amp;' ),
			array( '&quot;x&quot;', '"x"' ),
			array( '&#039;', "'" ),
			array( '<b>', '<b>' ),
			array( '&notanentity;', '&notanentity;' ),
		);
	}

	#[DataProvider( 'nonStringProvider' )]
	public function testNonStringInputIsHandled( $input, $expected ) {
		$this->assertSame( $expected, ceo_meta_for_editor( $input ) );
	}

	public static function nonStringProvider() {
		return array(
			array( null, '' ),
			array( false, '' ),
			array( 123, '123' ),
		);
	}

	public function testSaveHandlerUnslashesEditorTextExactlyOnce() {
		$post = $this->setGlobalPost( 17 );
		$this->grantCurrentUserCap( 'edit_post' );
		CE_Test_State::$valid_nonces['admin-meta.php'] = 'valid-nonce';
		$typed = "C:\\comics\\today's <b>note</b>";
		$_POST = array(
			'comic_nonce' => 'valid-nonce',
			'transcript'  => addslashes( $typed ),
		);

		ceo_handle_edit_save_comic( 17, $post );

		$this->assertSame( esc_textarea( $typed ), CE_Test_State::$post_meta['17:transcript'] );
		unset( $_POST );
	}

	public function testSaveHandlerRejectsAnInvalidNonce() {
		$post = $this->setGlobalPost( 17 );
		$this->grantCurrentUserCap( 'edit_post' );
		$_POST = array(
			'comic_nonce' => 'invalid',
			'transcript'  => 'must not be saved',
		);

		ceo_handle_edit_save_comic( 17, $post );

		$this->assertArrayNotHasKey( '17:transcript', CE_Test_State::$post_meta );
		unset( $_POST );
	}

	public function testSaveHandlerIgnoresArrayShapedMetaValues() {
		$post = $this->setGlobalPost( 17 );
		$this->grantCurrentUserCap( 'edit_post' );
		CE_Test_State::$valid_nonces['admin-meta.php'] = 'valid-nonce';
		$this->setPostMeta( 17, 'transcript', 'existing transcript' );
		$_POST = array(
			'comic_nonce' => 'valid-nonce',
			'transcript'  => array( 'unexpected value' ),
		);

		ceo_handle_edit_save_comic( 17, $post );

		$this->assertSame( 'existing transcript', CE_Test_State::$post_meta['17:transcript'] );
		unset( $_POST );
	}
}
