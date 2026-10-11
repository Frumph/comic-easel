<?php

/**
 * Cast Hover widget — widgets/casthover.php
 */
class CastHoverWidgetTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'widgets/casthover.php' );
		CE_Test_State::$is['single']      = true;
		CE_Test_State::$terms['characters'] = array(
			$this->makeTerm( 8, 'hero', 'Hero', 'characters' ),
			$this->makeTerm( 9, 'sidekick', 'Side & Kick', 'characters' ),
		);
	}

	private function render( $instance ) {
		$widget = new ceo_casthover_reference_widget();
		return $this->capture( function () use ( $widget, $instance ) {
			$widget->widget( self::widgetArgs(), $instance );
		} );
	}

	public function testRendersAnEmptyInstance() {
		// The widget has no update() of its own, and one that was never saved has no title.
		$this->setGlobalPost( 7 );
		$out = $this->render( array() );

		$this->assertStringContainsString( '<div class="castrefwidget-wrapper"><span class="castrefwidget-line casthover-hovercard-hook">', self::withoutKsesSentinel( $out ) );
		$this->assertStringNotContainsString( 'widget-title', $out );
	}

	public function testMarkupIsUnchangedByEscaping() {
		$this->setGlobalPost( 7 );
		$this->assertSameAsRecorded( 'casthover.html', $this->render( array( 'title' => 'Cast' ) ) );
	}

	public function testWrappersTitleAndHovercardsGoThroughKses() {
		$this->setGlobalPost( 7 );
		$this->render( array( 'title' => 'Cast' ) );

		$args = self::widgetArgs();
		$this->assertContains( $args['before_widget'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['before_title'] . 'Cast' . $args['after_title'], CE_Test_State::$kses_calls );
		$this->assertContains( $args['after_widget'], CE_Test_State::$kses_calls );
		$hovercards = array_filter( CE_Test_State::$kses_calls, function ( $call ) {
			return 0 === strpos( $call, '<span class="castrefwidget-line' );
		} );
		$this->assertCount( 1, $hovercards, 'the hovercards are filtered as one block' );
	}

	/**
	 * On the home page the widget shows the cast of the latest comic, and puts the page's own
	 * post back afterwards. With no comic published it used to read the ID of false.
	 */
	public function testHomePageWithNoComicsShowsNothing() {
		CE_Test_State::$is = array( 'home' => true );
		$page_post         = $this->makePost( 99, 'A blog post', array( 'post_type' => 'post' ) );
		$GLOBALS['post']   = $page_post;

		$this->assertSame( '', $this->render( array( 'title' => 'Cast' ) ) );
		$this->assertSame( $page_post, $GLOBALS['post'] );
		$this->assertNull( $GLOBALS['ceo_saved_post'] );
	}

	public function testHomePagePutsThePagesPostBack() {
		CE_Test_State::$is          = array( 'home' => true );
		CE_Test_State::$found_posts = array( $this->makePost( 7, 'The latest comic' ) );
		$page_post                  = $this->makePost( 99, 'A blog post', array( 'post_type' => 'post' ) );
		$GLOBALS['post']            = $page_post;

		$this->assertStringContainsString( 'castrefwidget-wrapper', $this->render( array() ) );
		$this->assertSame( $page_post, $GLOBALS['post'] );
		$this->assertNull( $GLOBALS['ceo_saved_post'] );
	}

	/** Every way out of the widget releases what ceo_protect() saved, not only the one that prints. */
	public function testEveryEarlyReturnReleasesTheSavedPost() {
		$this->setGlobalPost( 7 );
		CE_Test_State::$is = array( 'home' => true, 'paged' => true );
		$this->assertSame( '', $this->render( array() ) );
		$this->assertNull( $GLOBALS['ceo_saved_post'] );

		CE_Test_State::$is = array( 'page' => true );
		$this->assertSame( '', $this->render( array() ) );
		$this->assertNull( $GLOBALS['ceo_saved_post'] );
	}

	public function testCharacterListLabelIsEscaped() {
		// The hovercard version of the comic's character list, which the plugin prints itself.
		CE_Test_State::$translations['Characters'] = 'Personnages <b>';
		$this->setGlobalPost( 7 );

		$this->assertStringStartsWith( '<div class="comic-characters">Personnages &lt;b&gt;: <span class="casthover-hovercard-hook">', ceo_add_characters_hovercards( '' ) );
	}
}
