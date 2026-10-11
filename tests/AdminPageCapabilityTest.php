<?php

/**
 * The Config and Import pages — ceo-config.php, ceo-import.php
 *
 * Both are registered for edit_theme_options, which WordPress checks before it shows them.
 * They check it again where they act, so a request that reaches the page code some other
 * way still cannot change settings or create comics. The pages run as they are included,
 * so each case runs in its own process (CE_TestCase::runIsolated()).
 */
class AdminPageCapabilityTest extends CE_TestCase {

	private function configState( $can ) {
		return array(
			'valid_nonces'      => array( 'ceo-update-options' => 'good' ),
			'current_user_caps' => $can ? array( 'edit_theme_options' => true ) : array(),
			// No options/*.php templates to render: the save is what is under test.
			'pluginfo'          => array( 'plugin_path' => '/nonexistent-ce-test/', 'plugin_url' => 'https://example.test/' ),
			'options'           => array(
				'blog_charset'      => 'UTF-8',
				'comiceasel-config' => array(
					'click_comic_next'  => false,
					'enable_random_nav' => false,
					'disable_keynav'    => true,
				),
			),
		);
	}

	private function saveNavigation( $can, array $post, array $get = array() ) {
		return $this->runIsolated(
			array(
				'state'   => $this->configState( $can ),
				'post'    => array_merge( array( '_wpnonce' => 'good', 'action' => 'ceo_save_navigation' ), $post ),
				'get'     => array_merge( array( 'post_type' => 'comic', 'page' => 'comiceasel-config' ), $get ),
				'load'    => array( 'functions/library.php' ),
				'include' => 'ceo-config.php',
			)
		);
	}

	public function testConfigSaveWritesThePostedSettings() {
		$report = $this->saveNavigation( true, array( 'click_comic_next' => '1', 'graphic_navigation_directory' => 'default' ) );

		$this->assertNull( $report['died'] );
		$config = $report['options']['comiceasel-config'];
		$this->assertTrue( $config['click_comic_next'] );
		$this->assertFalse( $config['disable_keynav'], 'an unticked box is absent from the form post' );
		$this->assertSame( 'default', $config['graphic_navigation_directory'] );
		$this->assertStringContainsString( 'Comic Easel Settings SAVED!', $report['output'] );
		$this->assertStringContainsString( 'data-tab="navigation"', $report['output'] );
	}

	public function testConfigSaveIgnoresSettingsInTheQueryString() {
		$report = $this->saveNavigation( true, array( 'click_comic_next' => '1' ), array( 'enable_random_nav' => '1', 'graphic_navigation_directory' => 'evil' ) );

		$config = $report['options']['comiceasel-config'];
		$this->assertTrue( $config['click_comic_next'] );
		$this->assertFalse( $config['enable_random_nav'] );
		$this->assertArrayNotHasKey( 'graphic_navigation_directory', $config );
	}

	public function testConfigSaveWithoutTheCapabilityStopsBeforeWriting() {
		$report = $this->saveNavigation( false, array( 'click_comic_next' => '1', 'disable_keynav' => '1' ) );

		$this->assertSame( 'You do not have permission to change these settings.', $report['died'] );
		$this->assertSame( $this->configState( false )['options']['comiceasel-config'], $report['options']['comiceasel-config'] );
	}

	public function testConfigPageWithoutANonceSavesNothing() {
		$report = $this->saveNavigation( true, array( '_wpnonce' => 'forged', 'click_comic_next' => '1' ) );

		$this->assertNull( $report['died'] );
		$this->assertSame( $this->configState( true )['options']['comiceasel-config'], $report['options']['comiceasel-config'] );
		$this->assertStringNotContainsString( 'SAVED', $report['output'] );
	}

	/**
	 * The tab a link opens still comes from the query string; only settings moved to $_POST.
	 */
	public function testConfigTabStillComesFromTheQueryString() {
		$report = $this->runIsolated(
			array(
				'state'   => $this->configState( true ),
				'get'     => array( 'post_type' => 'comic', 'page' => 'comiceasel-config', 'tab' => 'archive' ),
				'load'    => array( 'functions/library.php' ),
				'include' => 'ceo-config.php',
			)
		);
		$this->assertStringContainsString( 'data-tab="archive"', $report['output'] );
	}

	private function import( $can, $nonce = 'good' ) {
		return $this->runIsolated(
			array(
				'state'   => array(
					'valid_nonces'      => array( 'comiceasel-import' => 'good' ),
					'current_user_caps' => $can ? array( 'edit_theme_options' => true ) : array(),
				),
				'post'    => array(
					'_wpnonce'           => $nonce,
					'import-type'        => 'namedate',
					'import-time'        => '00:01',
					'import-date-format' => 'Y-m-d',
					'import-date-mask'   => '{DATE}*.*',
					'import-chapter'     => '4',
				),
				'include' => 'ceo-import.php',
			)
		);
	}

	public function testImportRunsForAUserWithTheCapability() {
		$report = $this->import( true );
		$this->assertNull( $report['died'] );
		$this->assertStringContainsString( 'Import type: by Filename w/date', $report['output'] );
	}

	public function testImportWithoutTheCapabilityStopsBeforeImporting() {
		$report = $this->import( false );
		$this->assertSame( 'You do not have permission to import comics.', $report['died'] );
		$this->assertStringNotContainsString( 'Import type', $report['output'] );
	}

	public function testImportWithoutAValidNonceDoesNothing() {
		$report = $this->import( false, 'forged' );
		$this->assertNull( $report['died'] );
		$this->assertStringNotContainsString( 'Import type', $report['output'] );
		$this->assertStringContainsString( 'Comic Easel - Import', $report['output'] );
	}
}
