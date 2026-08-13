<?php
if (!defined('ABSPATH')) exit;

if ( isset( $_GET['latest'] ) )
	add_action( 'template_redirect', 'ceo_latest_comic_jump' );

if ( isset( $_GET['random'] ) )
	add_action( 'template_redirect', 'ceo_random_comic' );

if (isset($_GET['ceopaypalipn'])) 
	add_action('template_redirect', 'ceo_paypal_ipn');

//to use simply create a URL link to "/?latest"
function ceo_latest_comic_jump() {
	$chapter = 0; $respond = ''; 
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public read-only navigation URLs historically accept this query argument.
	if (isset($_GET['latest']) && is_scalar($_GET['latest'])) $chapter = sanitize_text_field(wp_unslash($_GET['latest']));
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
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public read-only navigation URLs historically accept this query argument.
	$chapter = isset($_GET['stay']) && is_scalar($_GET['stay']) ? intval(wp_unslash($_GET['stay'])) : 0;
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
	return ceo_paypal_ipn_item_type($item_name) === 'original';
}

/**
 * Resolve the product type from the label Comic Easel put at the start of an item name.
 *
 * PayPal returns the item name that was submitted with the cart. Only the two labels the
 * plugin itself creates are valid; treating every unknown value as a print disables several
 * server-side checks.
 */
function ceo_paypal_ipn_item_type($item_name) {
	$item_name = strtolower((string)$item_name);
	$labels = array(
		'original' => array(strtolower(__('Original','comiceasel')), 'original'),
		'print' => array(strtolower(__('Print','comiceasel')), 'print')
	);
	foreach ($labels as $type => $type_labels) {
		foreach (array_unique($type_labels) as $label) {
			if ($label !== '' && strpos($item_name, $label.' - ') === 0) return $type;
		}
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
	$item_type = ceo_paypal_ipn_item_type($item_name);
	if ($item_type === 'original') {
		$amount = get_post_meta($post_id, 'buy_print_orig_amount', true);
		if (empty($amount)) $amount = ceo_pluginfo('buy_comic_orig_amount');
	} elseif ($item_type === 'print') {
		$amount = get_post_meta($post_id, 'buy_print_amount', true);
		if (empty($amount)) $amount = ceo_pluginfo('buy_comic_print_amount');
	} else {
		return 0.0;
	}
	return (float)preg_replace('/[^0-9.]/', '', (string)$amount);
}

/**
 * Was this payment actually made to the site owner?
 *
 * The payee is a hidden form field too, so a buyer can redirect payment to
 * their own account and still have PayPal send us a genuine notification.
 * A missing address cannot be verified, so it must fail closed.
 */
function ceo_paypal_ipn_payee_matches($ipn, $comiceasel_config) {
	$expected = ceo_paypal_merchant($comiceasel_config);
	if ($expected === '') return false;
	foreach (array('receiver_email', 'business') as $field) {
		if (!empty($ipn[$field]) && strtolower(trim($ipn[$field])) == $expected) return true;
	}
	return false;
}

function ceo_paypal_merchant($comiceasel_config) {
	if (!is_array($comiceasel_config)) return '';
	$merchant = isset($comiceasel_config['buy_comic_email']) ? strtolower(trim((string)$comiceasel_config['buy_comic_email'])) : '';
	return ($merchant === '' || $merchant === 'yourname@yourpaypalemail.com') ? '' : $merchant;
}

/**
 * Return the explicit currency shared by the checkout form and IPN validator.
 */
function ceo_paypal_currency($comiceasel_config) {
	$currency = is_array($comiceasel_config) && isset($comiceasel_config['buy_comic_currency'])
		? strtoupper(trim((string)$comiceasel_config['buy_comic_currency']))
		: '';
	$currency = strtoupper(trim((string)apply_filters('ceo_paypal_expected_currency', $currency)));
	return preg_match('/^[A-Z]{3}$/', $currency) ? $currency : '';
}

/**
 * Validate one cart line entirely against server-side state.
 *
 * Returning the normalized values lets the endpoint validate the whole cart before it
 * performs any writes.
 */
function ceo_paypal_ipn_validate_item($post_id, $item_name, $comiceasel_config) {
	$post_id = (int)$post_id;
	$item_type = ceo_paypal_ipn_item_type($item_name);
	if ($post_id < 1 || !$item_type || !is_array($comiceasel_config)) return false;

	$post = get_post($post_id);
	if (empty($post) || is_wp_error($post) || $post->post_type !== 'comic' || $post->post_status !== 'publish') return false;
	if (!empty($post->post_password)) return false;

	$sell_key = ($item_type === 'original') ? 'buy_comic_sell_original' : 'buy_comic_sell_print';
	if (empty($comiceasel_config[$sell_key])) return false;

	$status_key = ($item_type === 'original') ? 'buyorig-status' : 'buyprint-status';
	$status = get_post_meta($post_id, $status_key, true);
	if ($status !== '' && !in_array($status, array(__('Available','comiceasel'), 'Available'), true)) return false;

	$amount = ceo_paypal_ipn_expected_amount($post_id, $item_name);
	if (!is_finite($amount) || $amount <= 0) return false;

	return array(
		'post_id' => $post_id,
		'type' => $item_type,
		'amount' => $amount
	);
}

/**
 * Validate every cart line atomically.
 */
function ceo_paypal_ipn_validate_cart($item_names, $item_numbers, $comiceasel_config) {
	if (!is_array($item_names) || !is_array($item_numbers) || empty($item_names) || count($item_names) !== count($item_numbers)) return false;

	$validated = array();
	$total = 0.0;
	foreach ($item_names as $key => $item_name) {
		if (!array_key_exists($key, $item_numbers)) return false;
		$item = ceo_paypal_ipn_validate_item($item_numbers[$key], $item_name, $comiceasel_config);
		if (!$item) return false;
		$total += $item['amount'];
		$validated[] = $item;
	}
	if (!is_finite($total) || $total <= 0) return false;
	return array('items' => $validated, 'total' => $total);
}

function ceo_paypal_ipn_amount_covers($payment_amount, $expected_total) {
	if (!is_numeric($payment_amount) || !is_finite((float)$payment_amount) || $expected_total <= 0) return false;
	return ((float)$payment_amount + 0.001) >= $expected_total;
}

function ceo_paypal_ipn() {
	$comiceasel_config = get_option('comiceasel-config');
	// Reject unusable stores before making an outbound verification request. The endpoint is
	// public even when the feature is not configured, so this also avoids needless remote
	// requests from arbitrary visitors.
	if (
		ceo_paypal_merchant($comiceasel_config) === ''
		|| ceo_paypal_currency($comiceasel_config) === ''
		|| (empty($comiceasel_config['buy_comic_sell_print']) && empty($comiceasel_config['buy_comic_sell_original']))
	) exit;

	// template_redirect fires for every front-end request, so this endpoint is
	// reachable by anyone. The payload is hostile until PayPal confirms it sent
	// it -- nothing below may touch the database or send mail before that.
	$ipn = array();
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- PayPal authenticates this webhook through ceo_paypal_ipn_verify().
	foreach ($_POST as $key => $value) {
		if (is_scalar($value)) $ipn[$key] = wp_unslash($value);
	}
	if (empty($ipn) || !ceo_paypal_ipn_verify($ipn)) exit;

	// The cart size is attacker-supplied even on a verified notification, so
	// clamp it rather than looping on whatever number arrived.
	$num_cart_items_raw = isset($ipn['num_cart_items']) ? trim((string)$ipn['num_cart_items']) : '1';
	$max_cart_items = max(1, (int)apply_filters('ceo_paypal_max_cart_items', 100));
	if (!ctype_digit($num_cart_items_raw)) exit;
	$num_cart_items = (int)$num_cart_items_raw;
	if ($num_cart_items < 1 || $num_cart_items > $max_cart_items) exit;

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
	if ($payment_status !== 'Completed' || trim((string)$txn_id) === '') exit;

	// PayPal retries notifications until the endpoint acknowledges them, and a
	// captured notification can be replayed by hand, so act on each transaction
	// exactly once.
	// Keep the status suffix for compatibility with the existing replay ledger. Only
	// Completed notifications reach this point, so a prior Pending notification cannot
	// suppress the eventual sale.
	$txn_key = $txn_id.'|'.$payment_status;
	$processed_txn = get_option('ceo_paypal_processed_txn', array());
	if (!is_array($processed_txn)) $processed_txn = array();
	if (in_array($txn_key, $processed_txn, true)) exit;

	$email_message = '';

	// A VERIFIED response only proves PayPal sent the notification. It says
	// nothing about who was paid or how much, both of which travel to PayPal as
	// hidden fields the buyer controls. Re-check them against the server side.
	$payee_ok = ceo_paypal_ipn_payee_matches($ipn, $comiceasel_config);

	$validated_cart = ceo_paypal_ipn_validate_cart($item_name, $item_number, $comiceasel_config);
	$cart_ok = ($validated_cart !== false);
	$expected_total = $cart_ok ? $validated_cart['total'] : 0.0;

	$expected_currency = ceo_paypal_currency($comiceasel_config);
	$payment_currency_uc = strtoupper(trim((string)$payment_currency));
	$currency_ok = ($payment_currency_uc !== '' && $payment_currency_uc === $expected_currency);

	// mc_gross includes shipping, so a total above the server-derived item prices is valid.
	$amount_ok = $cart_ok && ceo_paypal_ipn_amount_covers($payment_amount, $expected_total);

	if (!$payee_ok) $email_message .= __('REJECTED: payment was not made to the configured PayPal address.','comiceasel')."\r\n\r\n";
	if (!$cart_ok) $email_message .= __('REJECTED: the cart does not match an available Comic Easel product.','comiceasel')."\r\n\r\n";
	if (!$amount_ok) $email_message .= sprintf(__('REJECTED: amount paid (%1$s) is below the asking price (%2$s).','comiceasel'), $payment_amount, $expected_total)."\r\n\r\n";
	if (!$currency_ok) $email_message .= sprintf(__('REJECTED: payment currency (%1$s) is not the configured currency (%2$s).','comiceasel'), $payment_currency, $expected_currency)."\r\n\r\n";

	delete_option('ceo_paypal_receiver');
	if ($payee_ok && $cart_ok && $amount_ok && $currency_ok) {
		foreach ($validated_cart['items'] as $item) {
			if ($item['type'] === 'original') {
				update_post_meta($item['post_id'], 'buyorig-status', __('Sold','comiceasel'));
				$post_id = $item['post_id'];
				$email_message .= 'Comic ID #'.$post_id." Set to SOLD\r\n\r\n";
				// Flush the cache on the item in question.
				if (defined('WP_CACHE') && WP_CACHE == true && function_exists('wp_cache_no_postid')) {
					wp_cache_no_postid($post_id);
				}
			}
		}
		$processed_txn[] = $txn_key;
		// Keep the ledger bounded; PayPal only retries for a few days.
		if (count($processed_txn) > 500) $processed_txn = array_slice($processed_txn, -500);
		update_option('ceo_paypal_processed_txn', $processed_txn, false);
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
	update_option('ceo_paypal_receiver', $email_message);
	if (isset($comiceasel_config['buy_comic_email']))
		wp_mail($comiceasel_config['buy_comic_email'], __('Comic Easel: Notification of Transaction - Buy Comic','comiceasel'), $email_message);
	exit;
}
