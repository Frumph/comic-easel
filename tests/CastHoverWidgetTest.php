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

		$this->assertStringContainsString( '<div class="castrefwidget-wrapper"><span class="castrefwidget-line casthover-hovercard-hook">', $out );
		$this->assertStringNotContainsString( 'widget-title', $out );
	}
}
