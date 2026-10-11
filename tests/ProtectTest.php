<?php

/**
 * ceo_protect() / ceo_unprotect() — functions/library.php
 *
 * The saved $post and $wp_query live in plugin-prefixed globals. They used to be $__post
 * and $__wp_query, a prefix PHP reserves for itself.
 */
class ProtectTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wp_query'], $GLOBALS['ceo_saved_post'], $GLOBALS['ceo_saved_wp_query'] );
		parent::tearDown();
	}

	public function testUnprotectRestoresWhatProtectSaved() {
		$original            = $this->setGlobalPost( 1 );
		$query               = new stdClass();
		$GLOBALS['wp_query'] = $query;

		ceo_protect();
		$this->setGlobalPost( 2 );
		$GLOBALS['wp_query'] = new stdClass();
		ceo_unprotect();

		$this->assertSame( $original, $GLOBALS['post'] );
		$this->assertSame( $query, $GLOBALS['wp_query'] );
		$this->assertNull( $GLOBALS['ceo_saved_post'] );
		$this->assertNull( $GLOBALS['ceo_saved_wp_query'] );
	}

	public function testNoReservedDoubleUnderscoreGlobalIsUsed() {
		$this->setGlobalPost( 1 );
		ceo_protect();
		$this->assertArrayNotHasKey( '__post', $GLOBALS );
		$this->assertArrayNotHasKey( '__wp_query', $GLOBALS );
		ceo_unprotect();
	}
}
