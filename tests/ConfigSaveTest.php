<?php

/**
 * The Config page's tab saves — ceo-config.php
 *
 * Each tab is its own form and posts only its own fields, so a tab's save may only write the
 * settings on that tab: a text setting the post leaves out is stored as blank. The page runs
 * as it is included, so each case runs in its own process (CE_TestCase::runIsolated()).
 */
class ConfigSaveTest extends CE_TestCase {

	private function save( $action, array $post ) {
		return $this->runIsolated(
			array(
				'state'   => array(
					'valid_nonces'      => array( 'ceo-update-options' => 'good' ),
					'current_user_caps' => array( 'edit_theme_options' => true ),
					// No options/*.php templates to render: the save is what is under test.
					'pluginfo'          => array( 'plugin_path' => '/nonexistent-ce-test/', 'plugin_url' => 'https://example.test/' ),
					'options'           => array(
						'blog_charset'      => 'UTF-8',
						'comiceasel-config' => array(
							'custom_post_type_slug_name' => 'strips',
							'chapter_on_home'            => '4',
						),
					),
				),
				'post'    => array_merge( array( '_wpnonce' => 'good', 'action' => $action ), $post ),
				'get'     => array( 'post_type' => 'comic', 'page' => 'comiceasel-config' ),
				'load'    => array( 'functions/library.php' ),
				'include' => 'ceo-config.php',
			)
		);
	}

	/**
	 * The comic slug is set on the Archive tab. Saving the General tab used to store it as
	 * blank, which put the comics back under /comic/.
	 */
	public function testGeneralTabLeavesTheComicSlugAlone() {
		$report = $this->save(
			'ceo_save_general',
			array(
				'thumbnail_size_for_rss'         => 'full',
				'thumbnail_size_for_direct_rss'  => 'full',
				'thumbnail_size_for_archive'     => 'large',
				'thumbnail_size_for_facebook'    => 'large',
				'chapter_on_home'                => '0',
				'disable_comic_on_home_page'     => '1',
			)
		);

		$config = $report['options']['comiceasel-config'];
		$this->assertSame( 'strips', $config['custom_post_type_slug_name'] );
		$this->assertSame( '0', $config['chapter_on_home'], 'the General tab still saves its own fields' );
		$this->assertTrue( $config['disable_comic_on_home_page'] );
	}

	public function testArchiveTabStillSavesTheComicSlug() {
		$report = $this->save( 'ceo_save_archive', array( 'custom_post_type_slug_name' => 'Pages' ) );

		$this->assertSame( 'pages', $report['options']['comiceasel-config']['custom_post_type_slug_name'] );
	}
}
