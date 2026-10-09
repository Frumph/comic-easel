<?php

/**
 * Comic Blog Post widget — widgets/comicblogpost.php
 */
class BlogPostWidgetTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'widgets/comicblogpost.php' );
		CE_Test_State::$options['date_format'] = 'F j, Y';
		$GLOBALS['wp_query'] = (object) array( 'is_single' => false );
	}

	private function render( $instance ) {
		$widget = new ceo_comic_blog_post_widget();
		return $this->capture( function () use ( $widget, $instance ) {
			$widget->widget( self::widgetArgs(), $instance );
		} );
	}

	public function testRendersAnEmptyInstance() {
		// A Legacy Widget block that was never saved renders with no settings at all: every
		// option off, so the post content alone.
		$GLOBALS['post'] = $this->makePost( 7, 'A Comic', array( 'post_content' => '<p>The blog post.</p>', 'comment_status' => 'open' ) );
		$out = $this->render( array() );

		$this->assertSame( '<section id="ceo-widget-2" class="widget ceo-widget"><p>The blog post.</p></section>', self::withoutKsesSentinel( $out ) );
	}

	public function testUpdateStoresUncheckedBoxesAsFalse() {
		$widget = new ceo_comic_blog_post_widget();
		$out    = $widget->update( array( 'title' => 'Blog', 'showdate' => '1' ), array() );

		foreach ( array( 'onlyhome', 'showtitle', 'showcommentlink', 'hidecontent' ) as $key ) {
			$this->assertFalse( $out[ $key ], $key );
		}
		$this->assertTrue( $out['showdate'] );
		$this->assertSame( 'Blog', $out['title'] );
	}
}
