<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The PayPal IPN validation helpers — functions/redirects.php
 *
 * These decide whether an unauthenticated POST to /?ceopaypalipn is allowed to mark artwork
 * as sold. ceo_paypal_ipn() itself cannot be unit-tested — every path out of it ends in
 * exit(), which is a language construct and cannot be stubbed — which is exactly why the
 * decisions were extracted into these four functions.
 */
class PaypalIpnTest extends CE_TestCase {

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/redirects.php' );
	}

	/* ---------------------------------------------------------------- *
	 * ceo_paypal_ipn_verify()
	 * ---------------------------------------------------------------- */

	private function paypalReplies( $body, $code = 200 ) {
		CE_Test_State::$http_response = array(
			'body'     => $body,
			'response' => array( 'code' => $code ),
		);
	}

	#[DataProvider( 'verifiedBodyProvider' )]
	public function testOnlyAnExplicitVerifiedIsAccepted( $body, $expected ) {
		$this->paypalReplies( $body );
		$this->assertSame( $expected, ceo_paypal_ipn_verify( array( 'txn_id' => '1' ) ) );
	}

	public static function verifiedBodyProvider() {
		return array(
			'exact'             => array( 'VERIFIED', true ),
			'trailing newline'  => array( "VERIFIED\n", true ),
			'surrounding space' => array( '  VERIFIED  ', true ),
			'lowercase'         => array( 'verified', false ),
			'suffixed'          => array( 'VERIFIEDX', false ),
			'invalid'           => array( 'INVALID', false ),
			'empty'             => array( '', false ),
			'html error page'   => array( '<html>error</html>', false ),
		);
	}

	public function testANonOkResponseIsRejectedEvenWhenTheBodySaysVerified() {
		$this->paypalReplies( 'VERIFIED', 500 );
		$this->assertFalse( ceo_paypal_ipn_verify( array( 'txn_id' => '1' ) ) );
	}

	public function testATransportErrorIsRejected() {
		CE_Test_State::$http_response = new WP_Error( 'http', 'could not connect' );
		$this->assertFalse( ceo_paypal_ipn_verify( array( 'txn_id' => '1' ) ) );
	}

	/**
	 * PayPal only recognises the notification if it is echoed back unchanged. The original
	 * implementation of this handler urlencoded and stripslashed every field first, which is
	 * part of why validation never worked.
	 */
	public function testTheNotificationIsEchoedBackVerbatim() {
		$this->paypalReplies( 'VERIFIED' );
		$ipn = array(
			'txn_id'         => 'ABC123',
			'payment_status' => 'Completed',
			'address_street' => "12 O'Connell St",
			'mc_gross'       => '65.00',
		);
		ceo_paypal_ipn_verify( $ipn );

		$sent = CE_Test_State::$http_requests[0]['args']['body'];
		foreach ( $ipn as $key => $value ) {
			$this->assertSame( $value, $sent[ $key ], "$key must be echoed back unchanged" );
		}
	}

	public function testTheValidationCommandIsSentFirstAndCannotBeOverwritten() {
		$this->paypalReplies( 'VERIFIED' );
		// cmd is attacker-controlled: it arrives in the same POST as everything else.
		ceo_paypal_ipn_verify( array( 'cmd' => '_cart', 'txn_id' => '1' ) );

		$sent = CE_Test_State::$http_requests[0]['args']['body'];
		$this->assertSame( '_notify-validate', $sent['cmd'] );
		$this->assertSame( 'cmd', array_key_first( $sent ), 'PayPal expects cmd first' );
	}

	public function testTheEndpointDefaultsToPaypalAndIsFilterable() {
		$this->paypalReplies( 'VERIFIED' );
		ceo_paypal_ipn_verify( array( 'txn_id' => '1' ) );
		$this->assertSame( 'https://ipnpb.paypal.com/cgi-bin/webscr', CE_Test_State::$http_requests[0]['url'] );

		CE_Test_State::$filters['ceo_paypal_ipn_endpoint'] = 'http://localhost:8081/stub.php';
		ceo_paypal_ipn_verify( array( 'txn_id' => '1' ) );
		$this->assertSame( 'http://localhost:8081/stub.php', CE_Test_State::$http_requests[1]['url'] );
	}

	/* ---------------------------------------------------------------- *
	 * ceo_paypal_ipn_is_original()
	 * ---------------------------------------------------------------- */

	#[DataProvider( 'itemNameProvider' )]
	public function testOriginalIsIdentifiedByTheLeadingLabel( $item_name, $expected ) {
		$this->assertSame( $expected, ceo_paypal_ipn_is_original( $item_name ) );
	}

	public static function itemNameProvider() {
		return array(
			'an original'                   => array( 'Original - My Comic - 12', true ),
			'case insensitive'              => array( 'ORIGINAL - My Comic - 12', true ),
			// The bug this test exists for: the comic title sits inside the item name, so a
			// substring search found "original" in the title and priced every print as an
			// original.
			'a print of a comic titled "The Original Sin"' => array( 'Print - The Original Sin - 12', false ),
			'a plain print'                 => array( 'Print - My Comic - 12', false ),
			'a similar word'                => array( 'Originals - x', false ),
			'missing the separator'         => array( 'Original- x', false ),
			'leading whitespace'            => array( ' Original - x', false ),
			'empty'                         => array( '', false ),
			'null'                          => array( null, false ),
			'numeric'                       => array( 123, false ),
		);
	}

	/**
	 * The item name is built when the form renders, but __() here resolves when the
	 * notification arrives. Both labels must work, or changing the site language silently
	 * reprices every original already on sale.
	 */
	public function testBothTranslatedAndUntranslatedLabelsAreRecognised() {
		CE_Test_State::$translations['Original'] = 'Origineel';
		$this->assertTrue( ceo_paypal_ipn_is_original( 'Origineel - My Comic - 12' ) );
		$this->assertTrue( ceo_paypal_ipn_is_original( 'Original - My Comic - 12' ) );
		$this->assertFalse( ceo_paypal_ipn_is_original( 'Print - My Comic - 12' ) );
	}
}
