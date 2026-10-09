<?php

/**
 * Thumbnail widget — widgets/thumbnail.php
 *
 * The widget's saved settings only gain a key when its admin form is saved, so a widget
 * configured before an option was added carries no entry for it, and an unchecked checkbox is
 * missing from the submitted form altogether. Both render and update have to cope with the
 * absence. phpunit.xml sets failOnWarning, so an undefined-key read fails these tests outright.
 */
class ThumbnailWidgetTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		// ceo_protect()/ceo_unprotect(), which the render method wraps itself in.
		self::loadPluginFile( 'functions/library.php' );
		self::loadPluginFile( 'widgets/thumbnail.php' );
		WP_Query::$last_query = null;
	}

	/** The shape a widget saved between mid-2012 and June 2013 still has: everything but inhistory. */
	private function instanceSavedBeforeInHistoryExisted() {
		return array(
			'title'      => 'Latest',
			'thumbchapt' => '',
			'first'      => false,
			'random'     => false,
			'thumbcount' => 1,
			'linktitle'  => false,
			'centering'  => false,
			'secondary'  => false,
			'same'       => false,
		);
	}

	public function testRendersAnInstanceSavedBeforeTheInHistoryOptionExisted() {
		$this->setGlobalPost( 7 );
		$widget = new ceo_thumbnail_widget();
		$widget->widget( array(), $this->instanceSavedBeforeInHistoryExisted() );

		$query = WP_Query::$last_query;
		$this->assertSame( 'comic', $query['post_type'] );
		$this->assertSame( '', $query['month'], 'a missing inhistory key means the option is off' );
		$this->assertSame( '', $query['day'] );
		$this->assertSame( '', $query['orderby'] );
		$this->assertSame( '', $query['exclude'] );
	}

	public function testRendersAnEmptyInstance() {
		// A Legacy Widget block that was never saved renders with no settings at all.
		$this->setGlobalPost( 7 );
		$widget = new ceo_thumbnail_widget();
		$widget->widget( array(), array() );

		$query = WP_Query::$last_query;
		$this->assertSame( 'comic', $query['post_type'] );
		$this->assertSame( 1, $query['showposts'], 'the form default, not WP_Query\'s' );
		$this->assertSame( 'DESC', $query['order'] );
		$this->assertSame( '', $query['chapters'] );
	}

	public function testSavedSettingsStillWinOverTheDefaults() {
		$this->setGlobalPost( 7 );
		$widget   = new ceo_thumbnail_widget();
		$instance = array_merge( $this->instanceSavedBeforeInHistoryExisted(), array( 'inhistory' => true, 'same' => true, 'thumbcount' => 3 ) );
		$widget->widget( array(), $instance );

		$query = WP_Query::$last_query;
		$this->assertSame( 3, $query['showposts'] );
		$this->assertNotSame( '', $query['month'] );
		$this->assertNotSame( '', $query['day'] );
		$this->assertSame( 'rand', $query['orderby'] );
		$this->assertSame( 7, $query['exclude'], 'same-page exclusion uses the current post' );
	}

	public function testUpdateStoresUncheckedBoxesAsFalse() {
		// Browsers omit unchecked checkboxes from the form post, so only the text fields arrive.
		$widget = new ceo_thumbnail_widget();
		$out    = $widget->update( array( 'title' => 'Latest', 'thumbnail_size' => 'medium', 'thumbchapt' => '', 'thumbcount' => '2' ), array() );

		foreach ( array( 'first', 'random', 'linktitle', 'centering', 'showdate', 'secondary', 'same', 'inhistory' ) as $key ) {
			$this->assertArrayHasKey( $key, $out );
			$this->assertFalse( $out[ $key ], $key );
		}
		$this->assertSame( 'Latest', $out['title'] );
		$this->assertSame( 'medium', $out['thumbnail_size'] );
		$this->assertSame( 2, $out['thumbcount'] );
	}

	public function testUpdateStoresCheckedBoxesAsTrue() {
		$widget = new ceo_thumbnail_widget();
		$out    = $widget->update( array( 'title' => '', 'thumbnail_size' => 'thumbnail', 'thumbchapt' => '', 'thumbcount' => '1', 'inhistory' => '1', 'random' => '1' ), array() );

		$this->assertTrue( $out['inhistory'] );
		$this->assertTrue( $out['random'] );
		$this->assertFalse( $out['first'] );
	}

	public function testFormListsTheChapters() {
		CE_Test_State::$terms['chapters'] = array(
			$this->makeTerm( 3, 'book-one', 'Book One' ),
			$this->makeTerm( 5, 'tom-jerry', "Tom & Jerry's" ),
		);
		$widget = new ceo_thumbnail_widget();
		$out    = $this->capture( function () use ( $widget ) {
			$widget->form( array( 'thumbchapt' => 'tom-jerry' ) );
		} );

		$this->assertStringContainsString( '<option name="" >All</option><option name="book-one" value="book-one" > Book One </option><option name="tom-jerry" value="tom-jerry" selected> Tom &amp; Jerry&#039;s </option>', $out );
	}

	public function testUpdateWritesEveryKeyOverAnOldInstance() {
		// Re-saving is what repairs an instance from before an option existed.
		$widget = new ceo_thumbnail_widget();
		$out    = $widget->update( array( 'title' => 'Latest', 'thumbnail_size' => 'thumbnail', 'thumbchapt' => '', 'thumbcount' => '1' ), $this->instanceSavedBeforeInHistoryExisted() );

		$this->assertArrayHasKey( 'inhistory', $out );
		$this->assertFalse( $out['inhistory'] );
	}
}
