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
		$this->assertSame( 'var ceo_content_warning_text = "Warning, Mature Content.";', $inline['data'] );
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
		$this->assertSame( 1, preg_match( '/^var ceo_content_warning_text = (".*");$/', $data, $m ) );
		$this->assertSame(
			'Rated "M" <img src=x onerror=alert(1)></script>',
			json_decode( $m[1] )
		);
	}

	/* ---------------------------------------------------------------- jump menus */

	public function testJumpMenuScriptIsEnqueuedInTheFooter() {
		ceo_enqueue_jump_menu();
		$this->assertEnqueuedInFooter( 'ceo-jump-menu', 'js/jump-menu.js' );
		$this->assertSame( '9.9', CE_Test_State::$scripts['ceo-jump-menu']['ver'] );
	}

	/* ------------------------------------------------------------------- admin */

	public function testChapterRadioScriptIsEnqueuedRatherThanPrinted() {
		ob_start();
		ceo_change_chapter_to_radio();
		$printed = ob_get_clean();

		$this->assertSame( '', $printed );
		$this->assertEnqueuedInFooter( 'ceo-chapter-radio', 'js/chapter-radio.js', array( 'jquery' ) );
	}

	/**
	 * The meta box setup that used to queue the script from admin_footer runs on every post
	 * editor, whatever the post type, so it still loads on all of them and nowhere else.
	 */
	public function testChapterRadioScriptLoadsOnThePostEditorScreens() {
		foreach ( array( 'post.php', 'post-new.php' ) as $hook ) {
			CE_Test_State::$scripts = array();
			ceo_comic_editor_scripts( $hook );
			$this->assertEnqueuedInFooter( 'ceo-chapter-radio', 'js/chapter-radio.js', array( 'jquery' ) );
		}
		foreach ( array( 'edit.php', 'edit-tags.php', 'comment.php', 'index.php' ) as $hook ) {
			CE_Test_State::$scripts = array();
			ceo_comic_editor_scripts( $hook );
			$this->assertArrayNotHasKey( 'ceo-chapter-radio', CE_Test_State::$scripts, $hook );
		}
	}

	public function testComicListStylesheetLoadsOnTheComicScreensOnly() {
		$this->setPost( 6 );
		$this->setPost( 1, 'post' );
		$cases = array(
			array( 'edit.php', array( 'post_type' => 'comic' ), true ),
			array( 'edit.php', array( 'post_type' => 'comic', 'page' => 'comiceasel-config' ), true ),
			array( 'post-new.php', array( 'post_type' => 'comic' ), true ),
			array( 'post.php', array( 'post' => '6', 'action' => 'edit' ), true ),
			array( 'edit.php', array(), false ),
			array( 'post-new.php', array( 'post_type' => 'page' ), false ),
			array( 'post.php', array( 'post' => '1', 'action' => 'edit' ), false ),
		);
		$saved_get = $_GET;
		foreach ( $cases as $case ) {
			list( $GLOBALS['pagenow'], $_GET, $expected ) = $case;
			CE_Test_State::$styles = array();
			ceo_comic_editor_scripts( $GLOBALS['pagenow'] );
			$label = $GLOBALS['pagenow'] . '?' . http_build_query( $_GET );
			if ( $expected ) {
				$this->assertArrayHasKey( 'ceo-admin-editor-style', CE_Test_State::$styles, $label );
				$this->assertSame( 'https://example.test/wp-content/plugins/comic-easel/css/admin-editor.css', CE_Test_State::$styles['ceo-admin-editor-style']['src'] );
				$this->assertSame( '9.9', CE_Test_State::$styles['ceo-admin-editor-style']['ver'] );
			} else {
				$this->assertSame( array(), CE_Test_State::$styles, $label );
			}
		}
		$_GET = $saved_get;
		unset( $GLOBALS['pagenow'] );
	}

	/**
	 * The chapter list and a single chapter's edit page are the screens whose column filter
	 * used to enqueue this; both have the screen ID edit-chapters.
	 */
	public function testChaptersStylesheetLoadsOnTheChapterScreensOnly() {
		self::loadPluginFile( 'functions/admin-meta.php' );
		foreach ( array( 'edit-chapters' => true, 'edit-category' => false, 'edit-characters' => false, 'comic' => false ) as $id => $expected ) {
			CE_Test_State::$styles = array();
			CE_Test_State::$screen = (object) array( 'id' => $id );
			ceo_chapters_enqueue_style();
			$this->assertSame( $expected, isset( CE_Test_State::$styles['comiceasel-chapters-style'] ), $id );
		}
		CE_Test_State::$screen = null;
		CE_Test_State::$styles = array();
		ceo_chapters_enqueue_style();
		$this->assertSame( array(), CE_Test_State::$styles, 'no screen, no stylesheet' );
	}

	public function testCastHoverAssetsCarryThePluginPrefix() {
		self::loadPluginFile( 'widgets/casthover.php' );
		ceo_casthover_res_init_styles();
		ceo_casthover_res_init_scripts();

		$this->assertSame( array( 'ceo-casthover-css' ), array_keys( CE_Test_State::$registered_styles ) );
		$this->assertSame( 'https://example.test/wp-content/plugins/comic-easel/css/casthover.css', CE_Test_State::$registered_styles['ceo-casthover-css']['src'] );
		$this->assertSame( array( 'ceo-casthover-css' ), array_keys( CE_Test_State::$styles ) );
		$this->assertEnqueuedInFooter( 'ceo-casthover-js', 'js/casthoverfunc.js', array( 'jquery' ) );
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
	 * <script> tag do not count -- only template HTML and string literals that could print one,
	 * or an inline event handler attribute such as onchange=.
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
				if ( preg_match( '#<script|<style|javascript:|\son[a-z]+\s*=#i', $token[1] ) ) {
					$offenders[] = $relative . ':' . $token[2];
				}
			}
		}
		$this->assertSame( array(), $offenders );
	}

	/**
	 * Stylesheets and scripts are enqueued on wp_enqueue_scripts / admin_enqueue_scripts. These
	 * hooks run once the head is being printed or the page is ending, which is where the plugin
	 * used to enqueue from; nothing may be hooked to them again.
	 */
	public function testNothingIsHookedToThePrintOrFooterActions() {
		$late      = array( 'wp_print_styles', 'wp_print_scripts', 'admin_print_styles', 'admin_print_scripts', 'wp_footer', 'admin_footer' );
		$offenders = array();
		$files     = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( CE_PLUGIN_DIR, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $files as $file ) {
			$relative = substr( $file->getPathname(), strlen( CE_PLUGIN_DIR ) + 1 );
			if ( 'php' !== $file->getExtension() || preg_match( '#^(vendor|tests|\.[^/]+)/#', $relative ) ) {
				continue;
			}
			$tokens = array_values( array_filter( token_get_all( file_get_contents( $file->getPathname() ) ), function ( $t ) {
				return ! is_array( $t ) || ! in_array( $t[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true );
			} ) );
			foreach ( $tokens as $i => $token ) {
				if ( is_array( $token ) && T_STRING === $token[0] && 'add_action' === $token[1] && isset( $tokens[ $i + 2 ] ) && is_array( $tokens[ $i + 2 ] )
					&& in_array( trim( $tokens[ $i + 2 ][1], '\'"' ), $late, true ) ) {
					$offenders[] = $relative . ':' . $token[2];
				}
			}
		}
		$this->assertSame( array(), $offenders );
	}
}
