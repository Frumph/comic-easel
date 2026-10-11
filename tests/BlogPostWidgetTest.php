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

	private function everythingOn() {
		return array( 'title' => 'From the blog', 'onlyhome' => false, 'showtitle' => true, 'showdate' => true, 'showcommentlink' => true, 'hidecontent' => false );
	}

	private function comicPost() {
		return $this->makePost( 7, 'The <em>Big</em> Day', array( 'post_content' => '<p>The blog post.</p>', 'comment_status' => 'open' ) );
	}

	public function testSingleComicMarkupIsUnchangedByEscaping() {
		$GLOBALS['post'] = $this->comicPost();
		$this->assertSameAsRecorded( 'blogpost-single.html', $this->render( $this->everythingOn() ) );
	}

	private function onTheHomePage() {
		CE_Test_State::$is['home']   = true;
		CE_Test_State::$found_posts = array( $this->comicPost() );
		$GLOBALS['post']            = $this->makePost( 99, 'Not a comic', array( 'post_type' => 'page' ) );
	}

	public function testHomePageMarkupIsUnchangedByEscaping() {
		// Recorded when the home page left the heading out, so rendered here without one; the
		// heading has its own test below.
		$this->onTheHomePage();
		$this->assertSameAsRecorded( 'blogpost-home.html', $this->render( array_merge( $this->everythingOn(), array( 'title' => '' ) ) ) );
	}

	/**
	 * The heading was worked out on the home page but never printed there.
	 */
	public function testHomePageShowsTheHeadingAsASingleComicDoes() {
		$heading = '<section id="ceo-widget-2" class="widget ceo-widget"><div class="comic-post-widget-header">From the blog</div>' . "\r\n" . '<h3 class="comic-post-widget-title">';

		$this->onTheHomePage();
		$this->assertStringStartsWith( $heading, self::withoutKsesSentinel( $this->render( $this->everythingOn() ) ) );
		$this->assertContains( 'From the blog', CE_Test_State::$kses_calls );

		CE_Test_State::$is = array();
		$GLOBALS['post'] = $this->comicPost();
		$this->assertStringStartsWith( $heading, self::withoutKsesSentinel( $this->render( $this->everythingOn() ) ) );
	}

	public function testWrappersHeadingAndTitleGoThroughKses() {
		$GLOBALS['post'] = $this->comicPost();
		$this->render( $this->everythingOn() );

		$args = self::widgetArgs();
		$this->assertContains( $args['before_widget'], CE_Test_State::$kses_calls );
		$this->assertContains( 'From the blog', CE_Test_State::$kses_calls );
		$this->assertContains( $args['after_widget'], CE_Test_State::$kses_calls );
		// Once inside ceo_title_for_html(), once more where it is printed.
		$this->assertContains( CE_KSES_SENTINEL . 'The <em>Big</em> Day', CE_Test_State::$kses_calls );
	}

	public function testCommentLinkLabelsAreEscaped() {
		CE_Test_State::$translations['Comment']  = 'Commentaire <b>';
		CE_Test_State::$translations['Comments'] = 'Commentaires <b>';
		$GLOBALS['post'] = $this->comicPost();
		$out = $this->render( $this->everythingOn() );

		$this->assertStringContainsString(
			'<span class="comment-balloon comment-balloon-empty">&nbsp;</span>Commentaire &lt;b&gt;|<span class="comment-balloon">1</span> Commentaire &lt;b&gt;|<span class="comment-balloon">%</span> Commentaires &lt;b&gt;',
			$out
		);
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
