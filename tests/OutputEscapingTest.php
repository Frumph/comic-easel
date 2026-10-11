<?php

/**
 * Late output escaping for public title and taxonomy surfaces.
 */
class OutputEscapingTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'functions/shortcodes.php' );
		self::loadPluginFile( 'functions/redirects.php' );
	}

	public function testTitleAttributeCannotCreateANewAttribute() {
		CE_Test_State::$titles[7] = 'Boom" onmouseover="alert(1) & Co';

		$escaped = ceo_title_for_attribute( 7 );

		$this->assertSame( 'Boom&quot; onmouseover=&quot;alert(1) &amp; Co', $escaped );
		$this->assertStringNotContainsString( '" onmouseover="', $escaped );
	}

	public function testHtmlTitleUsesKsesWithoutDoubleEncodingEntities() {
		CE_Test_State::$titles[7] = '<em>A &amp; B</em><img src=x onerror=alert(1)>';

		$this->assertSame(
			CE_KSES_SENTINEL . '<em>A &amp; B</em><img src=x onerror=alert(1)>',
			ceo_title_for_html( 7 )
		);
		$this->assertSame(
			array( '<em>A &amp; B</em><img src=x onerror=alert(1)>' ),
			CE_Test_State::$kses_calls
		);
	}

	public function testCastOutputEscapesSlugAttributesAndFiltersVisibleMarkup() {
		$character              = new stdClass();
		$character->slug        = 'hero" onclick="alert(1)';
		$character->name        = '<em>Hero &amp; Friend</em><script>alert(1)</script>';
		$character->description = '<strong>Bio</strong><script>alert(2)</script>';
		$character->count       = 1;

		$output = ceo_cast_display( $character, false, true );

		$this->assertStringContainsString( 'character-hero&quot; onclick=&quot;alert(1)', $output );
		$this->assertStringNotContainsString( 'class="cast-pic character-hero" onclick="', $output );
		$this->assertStringContainsString( CE_KSES_SENTINEL . $character->name, $output );
		$this->assertStringContainsString( CE_KSES_SENTINEL . $character->description, $output );
	}

	public function testUnknownCharacterShortcodeEscapesTheReflectedName() {
		$output = ceo_cast_page(
			array( 'character' => '<img src=x onerror=alert(1)>' )
		);

		$this->assertStringContainsString( '&lt;img src=x onerror=alert(1)&gt;', $output );
		$this->assertStringNotContainsString( '<img', $output );
	}

	public function testBuyComicMessagesPreserveAllowedMarkupThroughKses() {
		$_REQUEST['action'] = 'thankyou';
		$message = '<em>Thanks &amp; welcome</em><script>alert(1)</script>';

		try {
			$output = ceo_display_buycomic( array( 'thanks' => $message ) );
		} finally {
			unset( $_REQUEST['action'] );
		}

		$this->assertStringContainsString( CE_KSES_SENTINEL . $message, $output );
		$this->assertContains( $message, CE_Test_State::$kses_calls );
	}

	/**
	 * The related-comics list is assembled from escaped pieces and then filtered again as it
	 * is echoed, so the escaping is visible at the sink.
	 */
	public function testRelatedComicsAreFilteredAsTheyAreEchoed() {
		self::loadPluginFile( 'functions/injections.php' );
		$this->setGlobalPost( 42 );
		$term          = new stdClass();
		$term->term_id = 9;
		CE_Test_State::$terms['characters'] = array( $term );
		$related             = new stdClass();
		$related->ID         = 7;
		$related->post_title = 'A " B';
		$this->useWpdbSpy()->result = array( $related );

		ob_start();
		ceo_display_related_comics();
		$output = ob_get_clean();

		$this->assertStringStartsWith( CE_KSES_SENTINEL . '<div class="related-comics">', $output );
		$this->assertStringContainsString( 'title="A &quot; B"', $output );
	}

	/**
	 * The version meta tag is filterable. Whatever a filter returns is held to <meta>.
	 */
	public function testVersionMetaIsHeldToMetaTags() {
		self::loadPluginFile( 'functions/injections.php' );
		CE_Test_State::$pluginfo['version'] = '1.0"><script>';

		ob_start();
		ceo_version_meta();
		ob_end_clean();

		$this->assertCount( 1, CE_Test_State::$kses_allowlist_calls );
		list( $version_tag, $allowed ) = CE_Test_State::$kses_allowlist_calls[0];
		$this->assertSame( '<meta name="Comic-Easel" content="1.0&quot;&gt;&lt;script&gt;" />' . "\r\n", $version_tag );
		$this->assertSame( array( 'meta' ), array_keys( $allowed ) );
	}
}
