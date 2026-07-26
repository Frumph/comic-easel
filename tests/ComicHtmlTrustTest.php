<?php

/**
 * ceo_comic_html_for_output() — functions/displaycomic.php
 *
 * The comic-html-above / comic-html-below fields are meant to hold markup, so their contents
 * cannot simply be escaped. Whether they are filtered instead depends on whether WordPress
 * would trust the people involved with raw HTML.
 *
 * Filtering happens at render rather than on save, deliberately: these meta keys are
 * unprotected on a post type declaring custom-fields support, so WordPress's own Custom
 * Fields panel writes them without ever passing through the plugin's save handler. A
 * save-time check would miss that path, and would also miss everything already in the
 * database.
 *
 * wp_kses_post() is stubbed as a recorder returning a sentinel, so these tests assert the
 * trust DECISION rather than re-implementing what kses strips.
 */
class ComicHtmlTrustTest extends CE_TestCase {

	const AUTHOR = 7;
	const EDITOR = 9;

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/displaycomic.php' );
	}

	private function post( $author = self::AUTHOR, $id = 1 ) {
		$post              = new stdClass();
		$post->ID          = $id;
		$post->post_author = $author;
		return $post;
	}

	private function assertWasFiltered( $result ) {
		$this->assertStringStartsWith( CE_KSES_SENTINEL, $result, 'value should have been filtered' );
	}

	private function assertWasNotFiltered( $result ) {
		$this->assertStringStartsNotWith( CE_KSES_SENTINEL, $result, 'value should have been returned as written' );
		$this->assertSame( array(), CE_Test_State::$kses_calls );
	}

	public function testMarkupFromAnUntrustedAuthorIsFiltered() {
		$out = ceo_comic_html_for_output( '<script>alert(1)</script>', $this->post() );
		$this->assertWasFiltered( $out );
	}

	public function testMarkupFromATrustedAuthorIsLeftAlone() {
		$this->grantCap( self::AUTHOR, 'unfiltered_html' );
		$out = ceo_comic_html_for_output( '<iframe src="x"></iframe>', $this->post() );
		$this->assertWasNotFiltered( $out );
	}

	/**
	 * The case this function exists to catch. On multisite an Editor does not hold
	 * unfiltered_html but can still edit an administrator's comic, so trusting the recorded
	 * post author alone let their markup through unfiltered.
	 */
	public function testMarkupIsFilteredWhenAnUntrustedUserEditedATrustedAuthorsComic() {
		$this->grantCap( self::AUTHOR, 'unfiltered_html' );
		$this->setPostMeta( 1, '_edit_last', self::EDITOR );
		$out = ceo_comic_html_for_output( '<script>alert(1)</script>', $this->post() );
		$this->assertWasFiltered( $out );
	}

	public function testMarkupIsLeftAloneWhenBothAuthorAndLastEditorAreTrusted() {
		$this->grantCap( self::AUTHOR, 'unfiltered_html' );
		$this->grantCap( self::EDITOR, 'unfiltered_html' );
		$this->setPostMeta( 1, '_edit_last', self::EDITOR );
		$out = ceo_comic_html_for_output( '<b>fine</b>', $this->post() );
		$this->assertWasNotFiltered( $out );
	}

	public function testLastEditorBeingTheAuthorIsNotTreatedAsASecondUser() {
		$this->grantCap( self::AUTHOR, 'unfiltered_html' );
		$this->setPostMeta( 1, '_edit_last', (string) self::AUTHOR );
		$out = ceo_comic_html_for_output( '<b>fine</b>', $this->post() );
		$this->assertWasNotFiltered( $out );
	}

	public function testAnUnknownPostIsFiltered() {
		$this->assertWasFiltered( ceo_comic_html_for_output( '<script>x</script>', null ) );
	}

	public function testAnAuthorlessPostIsFiltered() {
		$post = $this->post( 0 );
		$this->assertWasFiltered( ceo_comic_html_for_output( '<script>x</script>', $post ) );
	}

	/**
	 * Ordering matters and is easy to get wrong: the stored value is decoded BEFORE the trust
	 * decision, so an entity-encoded payload from an untrusted author must still reach kses
	 * as live markup rather than sneaking past as inert text.
	 */
	public function testEncodedMarkupIsDecodedBeforeBeingFiltered() {
		ceo_comic_html_for_output( '&lt;script&gt;alert(1)&lt;/script&gt;', $this->post() );
		$this->assertSame( array( '<script>alert(1)</script>' ), CE_Test_State::$kses_calls );
	}

	/** Only one level of encoding is peeled, so a doubly-encoded value stays inert. */
	public function testOnlyOneLevelOfEncodingIsDecoded() {
		ceo_comic_html_for_output( '&amp;lt;script&amp;gt;', $this->post() );
		$this->assertSame( array( '&lt;script&gt;' ), CE_Test_State::$kses_calls );
	}
}
