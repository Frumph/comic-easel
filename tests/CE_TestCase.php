<?php

use PHPUnit\Framework\TestCase;

/**
 * Base case: resets the stub state between tests and offers a few helpers for loading
 * plugin files once per process.
 */
abstract class CE_TestCase extends TestCase {

	/** @var array<string,bool> plugin files already required in this process */
	private static $loaded = array();

	protected function setUp(): void {
		parent::setUp();
		CE_Test_State::reset();
	}

	/**
	 * Require a plugin file, relative to the plugin root, at most once per process.
	 */
	protected static function loadPluginFile( $relative ) {
		if ( isset( self::$loaded[ $relative ] ) ) {
			return;
		}
		self::$loaded[ $relative ] = true;
		require_once CE_PLUGIN_DIR . '/' . $relative;
	}

	/**
	 * Run a plugin page or handler in a child PHP process through tests/isolated-runner.php,
	 * for code that declares functions when it is included or ends in exit(). Returns the
	 * runner's report: output, options, post meta, meta writes, mail, and whether the code
	 * exited or stopped in wp_die(). Anything the child prints to stderr -- a warning, a
	 * notice, a fatal -- fails the test, as it would in this process.
	 */
	protected function runIsolated( array $spec ) {
		$process = proc_open(
			array( PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', CE_PLUGIN_DIR . '/tests/isolated-runner.php' ),
			array( 0 => array( 'pipe', 'r' ), 1 => array( 'pipe', 'w' ), 2 => array( 'pipe', 'w' ) ),
			$pipes
		);
		fwrite( $pipes[0], json_encode( $spec ) );
		fclose( $pipes[0] );
		$stdout = stream_get_contents( $pipes[1] );
		$stderr = stream_get_contents( $pipes[2] );
		fclose( $pipes[1] );
		fclose( $pipes[2] );
		proc_close( $process );

		$this->assertSame( '', $stderr, 'the isolated run reported errors' );
		$report = json_decode( $stdout, true );
		$this->assertIsArray( $report, 'the isolated run printed no report: ' . $stdout );
		return $report;
	}

	/**
	 * Install a fresh $wpdb spy as the global and return it.
	 */
	protected function useWpdbSpy() {
		$spy             = new CE_WPDB_Spy();
		$GLOBALS['wpdb'] = $spy;
		return $spy;
	}

	/**
	 * Set the global $post to a lightweight stand-in. The functions under test only ever
	 * read ->ID and ->post_author, so a real WP_Post is unnecessary.
	 */
	protected function setGlobalPost( $id = 1, $author = 0 ) {
		$post               = new stdClass();
		$post->ID           = $id;
		$post->post_author  = $author;
		$post->post_type    = 'comic';
		$post->post_status  = 'publish';
		$GLOBALS['post']    = $post;
		return $post;
	}

	protected function setPostMeta( $post_id, $key, $value ) {
		CE_Test_State::$post_meta[ $post_id . ':' . $key ] = $value;
	}

	protected function setPost( $id, $type = 'comic', $status = 'publish', $password = '' ) {
		$post                = new stdClass();
		$post->ID            = $id;
		$post->post_type     = $type;
		$post->post_status   = $status;
		$post->post_password = $password;
		CE_Test_State::$posts[ $id ] = $post;
		return $post;
	}

	protected function grantCap( $user_id, $cap ) {
		CE_Test_State::$user_caps[ $user_id . ':' . $cap ] = true;
	}

	protected function grantCurrentUserCap( $cap ) {
		CE_Test_State::$current_user_caps[ $cap ] = true;
	}

	/**
	 * A post stand-in with the fields the widgets print, registered for get_post() and
	 * get_the_title().
	 */
	protected function makePost( $id, $title, array $fields = array() ) {
		$post = (object) array_merge(
			array(
				'ID'             => $id,
				'post_title'     => $title,
				'post_type'      => 'comic',
				'post_status'    => 'publish',
				'post_author'    => 0,
				'post_password'  => '',
				'post_content'   => '',
				'post_date'      => '2020-01-15 12:00:00',
				'comment_status' => 'closed',
			),
			$fields
		);
		CE_Test_State::$posts[ $id ]  = $post;
		CE_Test_State::$titles[ $id ] = $title;
		return $post;
	}

	protected function makeTerm( $term_id, $slug, $name, $taxonomy = 'chapters', array $fields = array() ) {
		return (object) array_merge(
			array(
				'term_id'  => $term_id,
				'slug'     => $slug,
				'name'     => $name,
				'taxonomy' => $taxonomy,
				'parent'   => 0,
				'count'    => 1,
			),
			$fields
		);
	}

	/** The wrappers a theme registers its sidebar with, as WordPress hands them to widget(). */
	protected static function widgetArgs() {
		return array(
			'before_widget' => '<section id="ceo-widget-2" class="widget ceo-widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		);
	}

	/** What $render printed. */
	protected function capture( callable $render ) {
		ob_start();
		try {
			$render();
		} finally {
			$printed = ob_get_clean();
		}
		return $printed;
	}

	/**
	 * Printed markup with the kses stubs' sentinel taken out: what real kses leaves of
	 * markup it finds nothing to strip from. Comparing that against output recorded before an
	 * escaping change shows the change left legitimate markup alone.
	 */
	protected static function withoutKsesSentinel( $html ) {
		return str_replace( CE_KSES_SENTINEL, '', $html );
	}

	/**
	 * Compare against tests/fixtures/widgets/$fixture: the markup the widget printed for the
	 * same test data before its output was escaped at the point of echo (recorded from
	 * 7252eb0 plus the settings-defaults change). A difference means escaping changed what a
	 * site shows, not just how it gets there.
	 */
	protected function assertSameAsRecorded( $fixture, $html ) {
		$this->assertStringEqualsFile( __DIR__ . '/fixtures/widgets/' . $fixture, self::withoutKsesSentinel( $html ) );
	}

	/**
	 * Assert that kses, given $allowed, would keep every tag and attribute in $html: each
	 * element must be in the allowlist and each of its attributes allowed for that element.
	 */
	protected function assertAllowedByKses( $html, array $allowed ) {
		preg_match_all( '#<(/?)([a-zA-Z][a-zA-Z0-9]*)([^>]*)>#', $html, $tags, PREG_SET_ORDER );
		$this->assertNotEmpty( $tags, 'no markup to check' );
		foreach ( $tags as $tag ) {
			$element = strtolower( $tag[2] );
			$this->assertArrayHasKey( $element, $allowed, "<$element> is not in the allowlist" );
			if ( '/' === $tag[1] ) {
				continue;
			}
			preg_match_all( '#([^\s=/]+)(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?#', $tag[3], $attrs );
			foreach ( $attrs[1] as $attr ) {
				$this->assertArrayHasKey( strtolower( $attr ), (array) $allowed[ $element ], "$attr on <$element> is not in the allowlist" );
			}
		}
	}
}
