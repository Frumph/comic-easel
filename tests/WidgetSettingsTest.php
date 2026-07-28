<?php

/**
 * Widget update() methods — widgets/navigation.php
 *
 * These decide what gets persisted from the widget admin form, so they are the storage half
 * of the escaping story. Characterization only: assertions here hold regardless of which
 * sanitiser each field ends up using.
 */
class WidgetSettingsTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'widgets/navigation.php' );
	}

	/** Every key the navigation widget's update() reads, so no test trips an undefined key. */
	private function navInstance( array $overrides = array() ) {
		$checkboxes = array(
			'previous_chap', 'next_chap', 'first_in', 'last_in', 'previous_in', 'next_in',
			'first', 'last', 'previous', 'next', 'random', 'archives', 'comments',
			'comictitle', 'comicchapter', 'imageurl', 'buycomic',
		);
		$text = array(
			'archive_path', 'previous_chap_title', 'next_chap_title', 'first_in_title',
			'last_in_title', 'previous_in_title', 'next_in_title', 'first_title',
			'last_title', 'previous_title', 'next_title', 'random_title',
			'archives_title', 'comments_title', 'buycomic_title',
		);
		$instance = array();
		foreach ( $checkboxes as $key ) {
			$instance[ $key ] = '1';
		}
		foreach ( $text as $key ) {
			$instance[ $key ] = '';
		}
		return array_merge( $instance, $overrides );
	}

	public function testNavigationWidgetStoresCheckboxesAsBooleans() {
		$widget = new ceo_comic_navigation_widget();
		$out    = $widget->update( $this->navInstance( array( 'random' => '1', 'first' => '0' ) ), array() );
		$this->assertTrue( $out['random'] );
		$this->assertFalse( $out['first'] );
	}

	public function testNavigationWidgetPreservesAnOrdinaryArchiveUrl() {
		$widget = new ceo_comic_navigation_widget();
		$out    = $widget->update( $this->navInstance( array( 'archive_path' => 'https://example.test/archive/' ) ), array() );
		$this->assertSame( 'https://example.test/archive/', $out['archive_path'] );
	}

	public function testNavigationWidgetPreservesARelativeArchivePath() {
		$widget = new ceo_comic_navigation_widget();
		$out    = $widget->update( $this->navInstance( array( 'archive_path' => '/archive/' ) ), array() );
		$this->assertSame( '/archive/', $out['archive_path'] );
	}

	public function testNavigationWidgetKeepsLinkLabelsAsText() {
		$widget = new ceo_comic_navigation_widget();
		$out    = $widget->update( $this->navInstance( array( 'first_title' => '&laquo; First' ) ), array() );
		// Whatever escaping is applied on save must survive a round trip through the form
		// without gaining a level of encoding each time.
		$this->assertSame( $out['first_title'], $widget->update( $this->navInstance( array( 'first_title' => $out['first_title'] ) ), array() )['first_title'] );
	}
}
