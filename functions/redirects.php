<?php

if ( isset( $_GET['latest'] ) )
	add_action( 'template_redirect', 'ceo_latest_comic_jump' );

if ( isset( $_GET['random'] ) )
	add_action( 'template_redirect', 'ceo_random_comic' );

if (isset($_GET['ceopaypalipn'])) 
	add_action('template_redirect', 'ceo_paypal_ipn');

//to use simply create a URL link to "/?latest"
function ceo_latest_comic_jump() {
	$chapter = 0; $respond = ''; 
	if (isset($_GET['latest'])) $chapter = esc_attr($_GET['latest']);
	if (isset($_GET['comment'])) $respond = '#respond';
	if (!empty($chapter)) {
		if (!is_numeric($chapter)) { //if the argument after latest is not a number, we assume it is the slugname
			$this_chapter = get_term_by('slug', $chapter, 'chapters'); //added: get chapter by slug
		} else {
			$this_chapter = get_term_by('term_id', $chapter, 'chapters'); //get chapter by id
		}
		$args = array( 
				'numberposts' => 1, 
				'post_type' => 'comic', 
				'orderby' => 'post_date', 
				'order' => 'DESC', 
				'post_status' => 'publish', 
				'chapters' => $this_chapter->slug
				);					
		$qposts = get_posts( $args );
		if (is_array($qposts)) {
			$qposts = reset($qposts);
			wp_redirect( get_permalink( $qposts->ID ).$respond );
		} else {
			wp_redirect( home_url() );
		}
	} else {
		$args = array( 
				'numberposts' => 1, 
				'post_type' => 'comic', 
				'orderby' => 'post_date', 
				'order' => 'DESC', 
				'post_status' => 'publish'
				);
		$qposts = get_posts( $args );
		if (is_array($qposts)) {
			$qposts = reset($qposts);
			wp_redirect( get_permalink( $qposts->ID ).$respond );
		} else {
			wp_redirect( home_url() );
		}
	}
	wp_reset_query();
	exit;
}

function ceo_random_comic() {
	if (isset($_GET['stay'])) $chapter = (int)esc_attr($_GET['stay']);
	if (!empty($chapter)) {
		$this_chapter = get_term_by('term_id', $chapter, 'chapters');
		$args = array( 
				'numberposts' => 1, 
				'post_type' => 'comic', 
				'orderby' => 'rand',  
				'post_status' => 'publish', 
				'chapters' => $this_chapter->slug
				);					
		$qposts = get_posts( $args );
		if (is_array($qposts)) {
			$qposts = reset($qposts);
			wp_redirect( get_permalink( $qposts->ID ) );
		} else {
			wp_redirect( bloginfo('url') );
		}
	} else {
		$args = array( 
				'numberposts' => 1, 
				'post_type' => 'comic', 
				'orderby' => 'rand', 
				'post_status' => 'publish'
				);	
	}
	$qposts = get_posts( $args );
	if (is_array($qposts)) {
		$qposts = reset($qposts);
		wp_redirect( get_permalink( $qposts->ID ) );
	}
	exit;
}

/**
 * Post an IPN payload back to PayPal and report whether PayPal owns it.
 *
 * PayPal requires the notification be echoed back verbatim, with
 * cmd=_notify-validate first, and answers with either VERIFIED or INVALID.
 * Anything that is not an explicit VERIFIED must be discarded.
 */
function ceo_paypal_ipn_verify($ipn) {
	// The notification is echoed back verbatim, except that cmd is ours and must stay ours.
	// $ipn is attacker-supplied, so a naive array_merge() with $ipn second lets a posted
	// cmd=_cart overwrite the validation command. That fails closed today, since PayPal will
	// not answer VERIFIED to a request that is not a validation request, but it is one
	// refactor away from being a bypass.
	$body = $ipn;
	unset($body['cmd']);
	$body = array_merge(array('cmd' => '_notify-validate'), $body);

	$endpoint = apply_filters('ceo_paypal_ipn_endpoint', 'https://ipnpb.paypal.com/cgi-bin/webscr');
	$response = wp_remote_post($endpoint, array(
			'timeout' => 30,
			'httpversion' => '1.1',
			'user-agent' => 'ComicEasel/'.ceo_pluginfo('version').'; '.home_url(),
			'body' => $body
			));
	if (is_wp_error($response)) return false;
	if (wp_remote_retrieve_response_code($response) != 200) return false;
	return (trim(wp_remote_retrieve_body($response)) === 'VERIFIED');
}

/**
 * Is this cart line an original rather than a print?
 *
 * ceo_display_buycomic() builds the item name as "<Original|Print> - <title> - <id>", so
 * match the leading label rather than searching the whole string: the title sits inside
 * the same field, and a comic called "The Original Sin" would otherwise make every print
 * purchase look like an original.
 */
function ceo_paypal_ipn_is_original($item_name) {
	$item_name = strtolower((string)$item_name);
	// Match the translated label AND the untranslated one. The item name was built when the
	// form rendered, but __() here resolves in whatever locale is active when the
	// notification arrives -- change the site language between those two moments and every
	// original would otherwise be misread as a print.
	foreach (array(strtolower(__('Original','comiceasel')), 'original') as $label) {
		if (strpos($item_name, $label.' - ') === 0) return true;
	}
	return false;
}

/**
 * The asking price for one cart line, taken from the server side only.
 *
 * The checkout form ships the price to the browser as a hidden field, so the
 * buyer can change it before it ever reaches PayPal. This re-derives what the
 * item should have cost from post meta, falling back to the plugin defaults --
 * the same lookup ceo_display_buycomic() uses to build the form.
 */
function ceo_paypal_ipn_expected_amount($post_id, $item_name) {
	// empty() rather than a === comparison, to match how ceo_display_buycomic() and the meta
	// box decide whether to fall back to the configured default. Diverging here would mean a
	// stored '0' showed the default price on the form but expected 0.00 from the payment,
	// which disables the amount check for the whole cart.
	if (ceo_paypal_ipn_is_original($item_name)) {
		$amount = get_post_meta($post_id, 'buy_print_orig_amount', true);
		if (empty($amount)) $amount = ceo_pluginfo('buy_comic_orig_amount');
	} else {
		$amount = get_post_meta($post_id, 'buy_print_amount', true);
		if (empty($amount)) $amount = ceo_pluginfo('buy_comic_print_amount');
	}
	return (float)preg_replace('/[^0-9.]/', '', (string)$amount);
}

/**
 * Was this payment actually made to the site owner?
 *
 * The payee is a hidden form field too, so a buyer can redirect payment to
 * their own account and still have PayPal send us a genuine notification.
 * Sites that never configured an address, or left the shipped placeholder in
 * place, have nothing to compare against and are not blocked.
 */
function ceo_paypal_ipn_payee_matches($ipn, $comiceasel_config) {
	$expected = isset($comiceasel_config['buy_comic_email']) ? strtolower(trim($comiceasel_config['buy_comic_email'])) : '';
	if (empty($expected) || $expected == 'yourname@yourpaypalemail.com') return true;
	foreach (array('receiver_email', 'business') as $field) {
		if (!empty($ipn[$field]) && strtolower(trim($ipn[$field])) == $expected) return true;
	}
	return false;
}

function ceo_paypal_ipn() {
	// template_redirect fires for every front-end request, so this endpoint is
	// reachable by anyone. The payload is hostile until PayPal confirms it sent
	// it -- nothing below may touch the database or send mail before that.
	$ipn = array();
	foreach ($_POST as $key => $value) {
		if (is_scalar($value)) $ipn[$key] = wp_unslash($value);
	}
	if (empty($ipn) || !ceo_paypal_ipn_verify($ipn)) exit;

	// The cart size is attacker-supplied even on a verified notification, so
	// clamp it rather than looping on whatever number arrived.
	$num_cart_items = isset($ipn['num_cart_items']) ? (int)$ipn['num_cart_items'] : 1;
	if ($num_cart_items < 1) $num_cart_items = 1;
	$max_cart_items = apply_filters('ceo_paypal_max_cart_items', 100);
	if ($num_cart_items > $max_cart_items) $num_cart_items = $max_cart_items;

	$count = 1;
	$item_name = array();
	$item_number = array();
	while ($count <= $num_cart_items) {
		$item_name[$count] = isset($ipn['item_name'.$count]) ? $ipn['item_name'.$count] : '';
		$item_number[$count] = isset($ipn['item_number'.$count]) ? $ipn['item_number'.$count] : '';
		$count++;
	}

	$payment_status = isset($ipn['payment_status']) ? $ipn['payment_status'] : '';
	$payment_amount = isset($ipn['mc_gross']) ? $ipn['mc_gross'] : '';
	$payment_currency = isset($ipn['mc_currency']) ? $ipn['mc_currency'] : '';
	$txn_id = isset($ipn['txn_id']) ? $ipn['txn_id'] : '';
	$shipping = isset($ipn['shipping']) ? $ipn['shipping'] : '';
	$business = isset($ipn['business']) ? $ipn['business'] : '';
	$payer_email = isset($ipn['payer_email']) ? $ipn['payer_email'] : '';
	$first_name = isset($ipn['first_name']) ? $ipn['first_name'] : '';
	$last_name = isset($ipn['last_name']) ? $ipn['last_name'] : '';
	$address_name = isset($ipn['address_name']) ? $ipn['address_name'] : '';
	$address_street = isset($ipn['address_street']) ? $ipn['address_street'] : '';
	$address_city = isset($ipn['address_city']) ? $ipn['address_city'] : '';
	$address_state = isset($ipn['address_state']) ? $ipn['address_state'] : '';
	$address_zip = isset($ipn['address_zip']) ? $ipn['address_zip'] : '';
	$address_country = isset($ipn['address_country']) ? $ipn['address_country'] : '';
	$memo = isset($ipn['memo']) ? $ipn['memo'] : '';

	// PayPal retries notifications until the endpoint acknowledges them, and a
	// captured notification can be replayed by hand, so act on each transaction
	// exactly once.
	// Key on the transaction AND its status: PayPal sends a fresh notification when a
	// payment moves Pending -> Completed, and keying on txn_id alone would discard the
	// Completed one as a duplicate, so the sale would never be recorded.
	$txn_key = $txn_id.'|'.$payment_status;
	$processed_txn = get_option('ceo_paypal_processed_txn', array());
	if (!is_array($processed_txn)) $processed_txn = array();
	if ($txn_id !== '' && in_array($txn_key, $processed_txn, true)) exit;

	$email_message = '';
	$comiceasel_config = get_option('comiceasel-config');

	// A VERIFIED response only proves PayPal sent the notification. It says
	// nothing about who was paid or how much, both of which travel to PayPal as
	// hidden fields the buyer controls. Re-check them against the server side.
	$payee_ok = ceo_paypal_ipn_payee_matches($ipn, $comiceasel_config);

	$expected_total = 0;
	for ($i = 1; $i <= $num_cart_items; $i++) {
		$pid = (int)$item_number[$i];
		if ($pid) $expected_total += ceo_paypal_ipn_expected_amount($pid, $item_name[$i]);
	}
	// mc_gross is a bare number, so comparing it to the asking price is only
	// meaningful once the currency matches. The buy form sends no currency_code,
	// which means PayPal settles these carts in the account's default -- but a
	// hand-built cart naming notify_url can pick any currency, and 65 JPY would
	// otherwise satisfy a $65 asking price.
	// Comparing mc_gross without the currency lets a payment in a weaker unit satisfy the
	// asking price numerically. There is nothing to compare against, though: the plugin has no
	// currency setting, and the buy form does not send one, so PayPal bills these carts in the
	// receiving account's own primary currency -- which is not USD for a seller outside the US.
	// Hardcoding a default would reject every legitimate sale for those sellers.
	//
	// So pin to whatever this site is actually paid in: remember the currency from the first
	// notification that carries one, and require later payments to match it. The first payment
	// after upgrading goes unchecked, which is no worse than the behaviour it replaces, and
	// every payment after that is covered without anyone having to configure anything.
	$expected_currency = strtoupper((string)apply_filters('ceo_paypal_expected_currency', get_option('ceo_paypal_currency', '')));
	$payment_currency_uc = strtoupper((string)$payment_currency);
	$currency_ok = ($payment_currency_uc === '') || ($expected_currency === '') || ($payment_currency_uc === $expected_currency);

	// mc_gross is the cart total and includes shipping, so it may legitimately
	// exceed the asking price -- only underpayment is rejected. A site with no
	// prices recorded has nothing to compare against and is not blocked.
	$amount_ok = ($expected_total <= 0) || (((float)$payment_amount + 0.001) >= $expected_total);

	if (!$payee_ok) $email_message .= __('REJECTED: payment was not made to the configured PayPal address.','comiceasel')."\r\n\r\n";
	if (!$amount_ok) $email_message .= sprintf(__('REJECTED: amount paid (%1$s) is below the asking price (%2$s).','comiceasel'), $payment_amount, $expected_total)."\r\n\r\n";
	if (!$currency_ok) $email_message .= sprintf(__('REJECTED: payment currency (%1$s) is not the %2$s this site is normally paid in.','comiceasel'), $payment_currency, $expected_currency)."\r\n\r\n";

	delete_option('ceo_paypal_receiver');
	if ($payment_status == 'Completed' && $payee_ok && $amount_ok && $currency_ok) {
		$count = 1;
		foreach ($item_number as $item_sub_number) {
			$post_id = (int)$item_number[$count];
			if ($post_id && ceo_paypal_ipn_is_original($item_name[$count])) {
				update_post_meta($post_id, 'buyorig-status', __('Sold','comiceasel'));
				$email_message .= 'Comic ID #'.$post_id." Set to SOLD\r\n\r\n";
				// Flush the cache on the item in question.
				if (defined('WP_CACHE') && WP_CACHE == true && function_exists('wp_cache_no_postid')) {
					wp_cache_no_postid($post_id);
				}
			}
			$count++;
		}
	}
	$email_message .= __('Transaction URL','comiceasel').': '.home_url()."\r\n";
	$email_message .= __('Number Items','comiceasel').': '.$num_cart_items."\r\n";
	$count = 1;
	foreach ($item_name as $item_sub_name) {
		$email_message .= __('Item Name','comiceasel').' ['.$count.']: '.$item_sub_name."\r\n";
		$email_message .= __('Item Number','comiceasel').' ['.$count.']: '.$item_number[$count]."\r\n";
		$count++;
	}
	$email_message .= __('Payment Status','comiceasel').': '.$payment_status."\r\n";
	$email_message .= __('Payment Amount','comiceasel').': '.$payment_amount."\r\n";
	$email_message .= __('Shipping','comiceasel').': '.$shipping."\r\n";
	$email_message .= __('Payment Currency','comiceasel').': '.$payment_currency."\r\n";
	$email_message .= __('TXN_ID','comiceasel').': '.$txn_id."\r\n";
	$email_message .= __('Paypal Receiver','comiceasel').': '.$business."\r\n\r\n";
	
	$email_message .= __('Payer Name','comiceasel').': '.$first_name.' '.$last_name."\r\n";
	$email_message .= __('Payer Email','comiceasel').': '.$payer_email."\r\n\r\n";
	
	$email_message .= __('to Name','comiceasel').': '.$address_name."\r\n";
	$email_message .= __('Street','comiceasel').': '.$address_street."\r\n";
	$email_message .= __('City','comiceasel').': '.$address_city."\r\n";
	$email_message .= __('State','comiceasel').': '.$address_state."\r\n";
	$email_message .= __('Zip','comiceasel').': '.$address_zip."\r\n";
	$email_message .= __('Country','comiceasel').': '.$address_country."\r\n\r\n";
	if (!empty($memo)) $email_message .= __('Memo','comiceasel').': '.$memo."\r\n";
	/*		foreach ($_POST as $post_info) {
				$email_message .= $post_info;
			} */
	// Remember the currency this site is paid in, so later payments can be checked against it.
	if ($currency_ok && $payment_currency_uc !== '' && $expected_currency === '') {
		update_option('ceo_paypal_currency', $payment_currency_uc, false);
	}
	if ($txn_id !== '') {
		$processed_txn[] = $txn_key;
		// Keep the ledger bounded; PayPal only retries for a few days.
		if (count($processed_txn) > 500) $processed_txn = array_slice($processed_txn, -500);
		update_option('ceo_paypal_processed_txn', $processed_txn, false);
	}
	update_option('ceo_paypal_receiver', $email_message);
	if (isset($comiceasel_config['buy_comic_email']))
		wp_mail($comiceasel_config['buy_comic_email'], __('Comic Easel: Notification of Transaction - Buy Comic','comiceasel'), $email_message);
	exit;
}
