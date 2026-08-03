<?php
/**
 * PHPUnit bootstrap for Comic Easel.
 *
 * WordPress is not loaded. See tests/stubs.php for the rationale and for the stubs the
 * tests depend on.
 */

// Plugin files guard themselves with `if (!defined('ABSPATH')) exit;`, so this has to be
// defined before any of them are required.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'ARRAY_N' ) ) {
	define( 'ARRAY_N', 'ARRAY_N' );
}

define( 'CE_PLUGIN_DIR', dirname( __DIR__ ) );

require_once __DIR__ . '/stubs.php';

/**
 * ceo_pluginfo() is the plugin's own config accessor. It lives in comiceasel.php, which
 * does far too much at load time to require in a unit test, so it is stubbed here and
 * driven from CE_Test_State::$pluginfo.
 */
if ( ! function_exists( 'ceo_pluginfo' ) ) {
	function ceo_pluginfo( $which = null ) {
		if ( null === $which ) {
			return CE_Test_State::$pluginfo;
		}
		return array_key_exists( $which, CE_Test_State::$pluginfo ) ? CE_Test_State::$pluginfo[ $which ] : '';
	}
}

/**
 * Plugin-local navigation helpers, stubbed so the archive listings can be called without
 * dragging in functions/navigation.php and its query dependencies. Not under test here.
 */
if ( ! function_exists( 'ceo_get_last_comic' ) ) {
	function ceo_get_last_comic( $in_same_chapter = false ) {
		return null;
	}
}

if ( ! function_exists( 'ceo_get_first_comic' ) ) {
	function ceo_get_first_comic( $in_same_chapter = false ) {
		return null;
	}
}

require_once __DIR__ . '/CE_TestCase.php';
