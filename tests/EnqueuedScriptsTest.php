<?php

/**
 * Scripts go through the enqueue API, never inline.
 *
 * WordPress.org asks plugins to load JavaScript and CSS with wp_enqueue_script() /
 * wp_enqueue_style() (plus wp_add_inline_script() for data) rather than printing <script> or
 * <style> blocks. These pin the places that used to print their own, and sweep the source so
 * a new one does not creep back in.
 */
class EnqueuedScriptsTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'functions/shortcodes.php' );
		self::loadPluginFile( 'ceo-admin.php' );
		$this->setGlobalPost( 1 );
		CE_Test_State::$pluginfo = array(
			'plugin_url' => 'https://example.test/wp-content/plugins/comic-easel/',
			'version'    => '9.9',
		);
	}

	private function assertEnqueuedInFooter( $handle, $file, $deps = array() ) {
		$this->assertArrayHasKey( $handle, CE_Test_State::$scripts, "$handle was not enqueued" );
		$script = CE_Test_State::$scripts[ $handle ];
		$this->assertSame( 'https://example.test/wp-content/plugins/comic-easel/' . $file, $script['src'] );
		$this->assertSame( $deps, $script['deps'] );
		$this->assertTrue( $script['args'], "$handle must load in the footer, after the markup it works on" );
		$this->assertFileExists( CE_PLUGIN_DIR . '/' . $file );
	}

	/* ---------------------------------------------------------------- transcript */

	public function testStyledTranscriptPrintsNoScriptAndEnqueuesItsToggle() {
		$this->setPostMeta( 1, 'transcript', 'Panel one.' );
		$out = ceo_the_transcript( 'styled' );

		$this->assertStringNotContainsStringIgnoringCase( '<script', $out );
		$this->assertStringNotContainsStringIgnoringCase( 'javascript:', $out );
		$this->assertStringContainsString( '<a href="#transcript-content" class="transcript-title"', $out );
		$this->assertEnqueuedInFooter( 'ceo-transcript', 'js/transcript.js' );
	}

	/**
	 * The markup is printed expanded and only the script collapses it, so a reader without
	 * JavaScript still gets the transcript.
	 */
	public function testStyledTranscriptIsNotCollapsedInTheMarkup() {
		$this->setPostMeta( 1, 'transcript', 'Panel one.' );
		$out = ceo_the_transcript( 'styled' );
		$this->assertStringContainsString( '<div id="transcript-content">Panel one.', $out );
		$this->assertStringNotContainsString( 'height', $out );
	}

	public function testPlainTranscriptModesEnqueueNothing() {
		$this->setPostMeta( 1, 'transcript', 'Panel one.' );
		ceo_the_transcript( 'raw' );
		ceo_the_transcript( 'br' );
		$this->assertSame( array(), CE_Test_State::$scripts );
	}

	public function testNoTranscriptEnqueuesNothing() {
		ceo_the_transcript( 'styled' );
		$this->assertSame( array(), CE_Test_State::$scripts );
	}

	/* ----------------------------------------------------------- content warning */

	public function testContentWarningTextIsAttachedAheadOfItsScript() {
		ob_start();
		ceo_content_warning_in_head();
		$printed = ob_get_clean();

		$this->assertSame( '', $printed, 'Nothing may be printed directly' );
		$this->assertCount( 1, CE_Test_State::$inline_scripts );
		$inline = CE_Test_State::$inline_scripts[0];
		$this->assertSame( 'ceo_comic_content_warning', $inline['handle'] );
		$this->assertSame( 'before', $inline['position'], 'content-warning.js reads the variable as soon as it runs' );
		$this->assertSame( 'var contentwarningtext = "Warning, Mature Content.";', $inline['data'] );
	}

	/**
	 * content-warning.js shows the text with textContent, so it arrives as plain text, in a
	 * string literal nothing can break out of.
	 */
	public function testContentWarningTextIsPlainTextInASafeLiteral() {
		CE_Test_State::$filters['ceo-content-warning'] = 'Rated "M" <img src=x onerror=alert(1)></script>';
		ceo_content_warning_in_head();
		$data = CE_Test_State::$inline_scripts[0]['data'];

		$this->assertStringNotContainsString( '<', $data );
		$this->assertStringNotContainsString( '</script', $data );
		$this->assertSame( 1, preg_match( '/^var contentwarningtext = (".*");$/', $data, $m ) );
		$this->assertSame(
			'Rated "M" <img src=x onerror=alert(1)></script>',
			json_decode( $m[1] )
		);
	}

	/* ------------------------------------------------------------------- admin */

	public function testChapterRadioScriptIsEnqueuedRatherThanPrinted() {
		ob_start();
		ceo_change_chapter_to_radio();
		$printed = ob_get_clean();

		$this->assertSame( '', $printed );
		$this->assertEnqueuedInFooter( 'ceo-chapter-radio', 'js/chapter-radio.js', array( 'jquery' ) );
	}

	public function testConfigScreenEnqueuesItsTabScriptAndStylesheet() {
		ob_start();
		ceo_config_enqueue_scripts();
		$printed = ob_get_clean();

		$this->assertSame( '', $printed, 'No <link> or <style> may be printed directly' );
		$this->assertEnqueuedInFooter( 'comiceasel-config-tabs', 'js/config-tabs.js', array( 'jquery' ) );
		$this->assertArrayHasKey( 'comiceasel-options-style', CE_Test_State::$styles );
	}

	/* ------------------------------------------------------------------- sweep */

	/**
	 * Every PHP file outside vendor/ and tests/, tokenised, so comments that merely mention a
	 * <script> tag do not count -- only template HTML and string literals that could print one.
	 */
	public function testNoPluginSourcePrintsInlineScriptOrStyle() {
		$offenders = array();
		$files     = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( CE_PLUGIN_DIR, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $files as $file ) {
			$relative = substr( $file->getPathname(), strlen( CE_PLUGIN_DIR ) + 1 );
			if ( 'php' !== $file->getExtension() || preg_match( '#^(vendor|tests|\.[^/]+)/#', $relative ) ) {
				continue;
			}
			foreach ( token_get_all( file_get_contents( $file->getPathname() ) ) as $token ) {
				if ( ! is_array( $token ) || ! in_array( $token[0], array( T_INLINE_HTML, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE ), true ) ) {
					continue;
				}
				if ( preg_match( '#<script|<style|javascript:#i', $token[1] ) ) {
					$offenders[] = $relative . ':' . $token[2];
				}
			}
		}
		$this->assertSame( array(), $offenders );
	}
}
