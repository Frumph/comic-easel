<?php

/**
 * ceo_clear_post_thumbnail_on_comics() — functions/filters.php
 *
 * The General tab's "Remove featured image in posts on non-ComicPress themes?" setting hides
 * the featured image on a single comic, where the comic itself is already shown. It is a
 * post_thumbnail_html filter, so it sees every featured image the site prints.
 */
class RemovePostThumbnailTest extends CE_TestCase {

	const IMAGE = '<img src="https://example.test/featured.png" class="wp-post-image" alt="" />';

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/filters.php' );
		CE_Test_State::$pluginfo['remove_post_thumbnail'] = true;
	}

	public function testRemovesTheFeaturedImageOnASingleComic() {
		CE_Test_State::$is['single'] = true;
		$this->setGlobalPost( 7 );

		$this->assertSame( '', ceo_clear_post_thumbnail_on_comics( self::IMAGE ) );
	}

	/**
	 * Everywhere else the image used to come back as null, so with the setting on no
	 * featured image showed anywhere on the site.
	 */
	public function testKeepsTheFeaturedImageOfABlogPost() {
		CE_Test_State::$is['single'] = true;
		$this->setGlobalPost( 7 )->post_type = 'post';

		$this->assertSame( self::IMAGE, ceo_clear_post_thumbnail_on_comics( self::IMAGE ) );
	}

	public function testKeepsFeaturedImagesOffSingleComicPages() {
		$this->setGlobalPost( 7 );

		$this->assertSame( self::IMAGE, ceo_clear_post_thumbnail_on_comics( self::IMAGE ) );
	}

	public function testDoesNothingWithTheSettingOff() {
		CE_Test_State::$pluginfo['remove_post_thumbnail'] = false;
		CE_Test_State::$is['single']                      = true;
		$this->setGlobalPost( 7 );

		$this->assertSame( self::IMAGE, ceo_clear_post_thumbnail_on_comics( self::IMAGE ) );
	}
}
