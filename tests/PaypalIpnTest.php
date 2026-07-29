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

	public function testUnknownItemLabelsAreNotTreatedAsPrints() {
		CE_Test_State::$pluginfo['buy_comic_print_amount'] = '25.00';
		$this->assertFalse( ceo_paypal_ipn_item_type( 'Poster - My Comic - 7' ) );
		$this->assertSame( 0.0, ceo_paypal_ipn_expected_amount( 7, 'Poster - My Comic - 7' ) );
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

	/* ---------------------------------------------------------------- *
	 * ceo_paypal_ipn_expected_amount()
	 * ---------------------------------------------------------------- */

	public function testOriginalAndPrintPricesComeFromTheirOwnMetaKeys() {
		$this->setPostMeta( 7, 'buy_print_orig_amount', '65.00' );
		$this->setPostMeta( 7, 'buy_print_amount', '25.00' );

		$this->assertSame( 65.0, ceo_paypal_ipn_expected_amount( 7, 'Original - x - 7' ) );
		$this->assertSame( 25.0, ceo_paypal_ipn_expected_amount( 7, 'Print - x - 7' ) );
	}

	public function testPriceFallsBackToTheConfiguredDefault() {
		CE_Test_State::$pluginfo['buy_comic_orig_amount']  = '80.00';
		CE_Test_State::$pluginfo['buy_comic_print_amount'] = '30.00';

		$this->assertSame( 80.0, ceo_paypal_ipn_expected_amount( 7, 'Original - x - 7' ) );
		$this->assertSame( 30.0, ceo_paypal_ipn_expected_amount( 7, 'Print - x - 7' ) );
	}

	/**
	 * A stored '0' must fall back to the configured default, because that is what the buy
	 * form and the meta box both do. Diverging would mean the form advertised one price while
	 * the IPN expected 0.00 — which switches the amount check off for the entire cart.
	 */
	public function testAStoredZeroFallsBackTheSameWayTheFormDoes() {
		$this->setPostMeta( 7, 'buy_print_orig_amount', '0' );
		CE_Test_State::$pluginfo['buy_comic_orig_amount'] = '65.00';
		$this->assertSame( 65.0, ceo_paypal_ipn_expected_amount( 7, 'Original - x - 7' ) );
	}

	#[DataProvider( 'messyAmountProvider' )]
	public function testAmountsAreParsedLeniently( $stored, $expected ) {
		$this->setPostMeta( 7, 'buy_print_amount', $stored );
		$this->assertSame( $expected, ceo_paypal_ipn_expected_amount( 7, 'Print - x - 7' ) );
	}

	public static function messyAmountProvider() {
		return array(
			'thousands separator' => array( '$1,299.00', 1299.0 ),
			'currency suffix'     => array( '65.00 USD', 65.0 ),
			'plain'               => array( '42', 42.0 ),
			'not a number'        => array( 'abc', 0.0 ),
		);
	}

	/* ---------------------------------------------------------------- *
	 * ceo_paypal_ipn_payee_matches()
	 * ---------------------------------------------------------------- */

	public function testPayeeMatchesOnEitherReceiverEmailOrBusiness() {
		$config = array( 'buy_comic_email' => 'me@example.com' );

		$this->assertTrue( ceo_paypal_ipn_payee_matches( array( 'receiver_email' => ' ME@Example.com ' ), $config ) );
		$this->assertTrue( ceo_paypal_ipn_payee_matches( array( 'business' => 'me@example.com' ), $config ) );
		$this->assertFalse( ceo_paypal_ipn_payee_matches( array( 'receiver_email' => 'thief@evil.test' ), $config ) );
		$this->assertFalse( ceo_paypal_ipn_payee_matches( array(), $config ) );
	}

	#[DataProvider( 'unconfiguredPayeeProvider' )]
	public function testPayeeCheckFailsClosedWhenNothingIsConfigured( $config ) {
		$this->assertFalse( ceo_paypal_ipn_payee_matches( array( 'receiver_email' => 'anyone@evil.test' ), $config ) );
	}

	public static function unconfiguredPayeeProvider() {
		return array(
			'option never saved'      => array( false ),
			'key absent'             => array( array() ),
			'empty string'           => array( array( 'buy_comic_email' => '' ) ),
			'shipped placeholder'    => array( array( 'buy_comic_email' => 'yourname@yourpaypalemail.com' ) ),
			'placeholder, odd case'  => array( array( 'buy_comic_email' => 'YourName@YourPayPalEmail.com' ) ),
		);
	}

	/* ---------------------------------------------------------------- *
	 * Full server-side cart validation
	 * ---------------------------------------------------------------- */

	private function validStoreConfig() {
		return array(
			'buy_comic_email'         => 'merchant@example.com',
			'buy_comic_currency'      => 'USD',
			'buy_comic_sell_print'    => true,
			'buy_comic_sell_original' => true,
		);
	}

	public function testCurrencyMustBeExplicitAndThreeLetters() {
		$this->assertSame( '', ceo_paypal_currency( array() ) );
		$this->assertSame( '', ceo_paypal_currency( array( 'buy_comic_currency' => 'US' ) ) );
		$this->assertSame( 'CAD', ceo_paypal_currency( array( 'buy_comic_currency' => ' cad ' ) ) );

		CE_Test_State::$filters['ceo_paypal_expected_currency'] = 'EUR';
		$this->assertSame( 'EUR', ceo_paypal_currency( array( 'buy_comic_currency' => 'USD' ) ) );
	}

	public function testAValidPublishedAvailableComicIsAccepted() {
		$this->setPost( 7 );
		$this->setPostMeta( 7, 'buy_print_orig_amount', '65.00' );

		$item = ceo_paypal_ipn_validate_item( 7, 'Original - My Comic - 7', $this->validStoreConfig() );

		$this->assertSame(
			array( 'post_id' => 7, 'type' => 'original', 'amount' => 65.0 ),
			$item
		);
	}

	#[DataProvider( 'invalidPostProvider' )]
	public function testOnlyPublicUnprotectedComicsAreAccepted( $type, $status, $password ) {
		$this->setPost( 7, $type, $status, $password );
		$this->setPostMeta( 7, 'buy_print_orig_amount', '65.00' );

		$this->assertFalse(
			ceo_paypal_ipn_validate_item( 7, 'Original - My Comic - 7', $this->validStoreConfig() )
		);
	}

	public static function invalidPostProvider() {
		return array(
			'ordinary post'       => array( 'post', 'publish', '' ),
			'draft comic'         => array( 'comic', 'draft', '' ),
			'private comic'       => array( 'comic', 'private', '' ),
			'password protected'  => array( 'comic', 'publish', 'secret' ),
		);
	}

	public function testDisabledSoldUnknownAndZeroPricedProductsAreRejected() {
		$this->setPost( 7 );
		$this->setPostMeta( 7, 'buy_print_orig_amount', '65.00' );
		$config = $this->validStoreConfig();

		$config['buy_comic_sell_original'] = false;
		$this->assertFalse( ceo_paypal_ipn_validate_item( 7, 'Original - x - 7', $config ) );

		$config['buy_comic_sell_original'] = true;
		$this->setPostMeta( 7, 'buyorig-status', 'Sold' );
		$this->assertFalse( ceo_paypal_ipn_validate_item( 7, 'Original - x - 7', $config ) );

		$this->setPostMeta( 7, 'buyorig-status', '' );
		$this->assertFalse( ceo_paypal_ipn_validate_item( 7, 'Poster - x - 7', $config ) );

		$this->setPostMeta( 7, 'buy_print_orig_amount', 'not a price' );
		$this->assertFalse( ceo_paypal_ipn_validate_item( 7, 'Original - x - 7', $config ) );
	}

	public function testOneBadLineRejectsTheEntireCart() {
		$this->setPost( 7 );
		$this->setPostMeta( 7, 'buy_print_orig_amount', '65.00' );

		$this->assertFalse(
			ceo_paypal_ipn_validate_cart(
				array( 1 => 'Original - Good - 7', 2 => 'Original - Missing - 8' ),
				array( 1 => 7, 2 => 8 ),
				$this->validStoreConfig()
			)
		);
	}

	public function testCartTotalsAndAmountChecksUseServerPrices() {
		$this->setPost( 7 );
		$this->setPost( 8 );
		$this->setPostMeta( 7, 'buy_print_orig_amount', '65.00' );
		$this->setPostMeta( 8, 'buy_print_amount', '25.00' );

		$cart = ceo_paypal_ipn_validate_cart(
			array( 1 => 'Original - One - 7', 2 => 'Print - Two - 8' ),
			array( 1 => 7, 2 => 8 ),
			$this->validStoreConfig()
		);

		$this->assertSame( 90.0, $cart['total'] );
		$this->assertFalse( ceo_paypal_ipn_amount_covers( '89.99', $cart['total'] ) );
		$this->assertTrue( ceo_paypal_ipn_amount_covers( '95.00', $cart['total'] ) );
		$this->assertFalse( ceo_paypal_ipn_amount_covers( '95 dollars', $cart['total'] ) );
	}
}
