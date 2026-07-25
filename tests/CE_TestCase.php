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

	protected function grantCap( $user_id, $cap ) {
		CE_Test_State::$user_caps[ $user_id . ':' . $cap ] = true;
	}
}
