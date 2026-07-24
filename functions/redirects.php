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
	$endpoint = apply_filters('ceo_paypal_ipn_endpoint', 'https://ipnpb.paypal.com/cgi-bin/webscr');
	$response = wp_remote_post($endpoint, array(
			'timeout' => 30,
			'httpversion' => '1.1',
			'user-agent' => 'ComicEasel/'.ceo_pluginfo('version').'; '.home_url(),
			'body' => array_merge(array('cmd' => '_notify-validate'), $ipn)
			));
	if (is_wp_error($response)) return false;
	if (wp_remote_retrieve_response_code($response) != 200) return false;
	return (trim(wp_remote_retrieve_body($response)) === 'VERIFIED');
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
	if (strstr(strtolower($item_name), 'original')) {
		$amount = get_post_meta($post_id, 'buy_print_orig_amount', true);
		if ($amount === '' || $amount === false) $amount = ceo_pluginfo('buy_comic_orig_amount');
	} else {
		$amount = get_post_meta($post_id, 'buy_print_amount', true);
		if ($amount === '' || $amount === false) $amount = ceo_pluginfo('buy_comic_print_amount');
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
	// mc_gross is the cart total and includes shipping, so it may legitimately
	// exceed the asking price -- only underpayment is rejected. A site with no
	// prices recorded has nothing to compare against and is not blocked.
	$amount_ok = ($expected_total <= 0) || (((float)$payment_amount + 0.001) >= $expected_total);

	if (!$payee_ok) $email_message .= __('REJECTED: payment was not made to the configured PayPal address.','comiceasel')."\r\n\r\n";
	if (!$amount_ok) $email_message .= sprintf(__('REJECTED: amount paid (%1$s) is below the asking price (%2$s).','comiceasel'), $payment_amount, $expected_total)."\r\n\r\n";

	delete_option('ceo_paypal_receiver');
	if ($payment_status == 'Completed' && $payee_ok && $amount_ok) {
		$count = 1;
		foreach ($item_number as $item_sub_number) {
			$post_id = (int)$item_number[$count];
			if ($post_id && strstr(strtolower($item_name[$count]), 'original')) {
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
	update_option('ceo_paypal_receiver', $email_message);
	if (isset($comiceasel_config['buy_comic_email']))
		wp_mail($comiceasel_config['buy_comic_email'], __('Comic Easel: Notification of Transaction - Buy Comic','comiceasel'), $email_message);
	exit;
}

/***
 * 1) Install and activate the Comic Easel plugin WITH webcomic stil active.
 * 2) In your browser bar you would type http://yoururl.com/?wc2ce&name=webcomic1
 * * the webcomic1 denotes the first comic in the webcomic plugin, there are several webcomic sets up, but they're always incremented by 1
 * so the next comic that was setup would be webcomic2  so  /?wc2ce&name=webcomic2  would trigger the migration of that one
 * 3) once you do that it should pause a few while loading your site, once it's done your site will finish loading. 
 * 4) Deactivate the webcomic plugin and switch to the comicpress theme.
 * 5) verify the comics are all there, the characters have been migrated and the storyline's are all chapters
 * **/

/*
if ( isset( $_GET['wc2ce'] ) )
	add_action( 'template_redirect', 'ceo_convert_to_ce' );

function ceo_convert_to_ce() {
	global $wpdb;
	if (isset($_REQUEST['name'])) {
		$name = esc_attr($_REQUEST['name']);
		if (!empty($name)) {
			// SQL Convert the characters and story
			$sql = "UPDATE {$wpdb->term_taxonomy} SET taxonomy='characters' WHERE taxonomy='".$name.'_character'."';";
			$wpdb->query($sql);
			$sql = "UPDATE {$wpdb->term_taxonomy} SET taxonomy='chapters' WHERE taxonomy='".$name.'_storyline'."';";
			$wpdb->query($sql);
			// ---
			$args = array(
					'posts_per_page'   => -1,
					'orderby'          => 'post_date',
					'order'            => 'DESC',
					'post_type'        => $name,
					'post_status'      => 'any',
					'suppress_filters' => true 
					);
			$qposts = get_posts( $args );
			// Loop through all posts and set whatever attachment is first found as the featured image
			foreach ($qposts as $qpost) {
				$attachments = get_posts(array(
							'post_type' => 'attachment', 
							'post_mime_type'=>'image', 
							'posts_per_page' => 0, 
							'post_parent' => $qpost->ID, 
							'order'=>'ASC'
							));
				if ($attachments) {
					foreach ($attachments as $attachment) {
						set_post_thumbnail($qpost->ID, $attachment->ID);
						break;
					}
				}
			}
			// Now set all comics as the 'comic' post type in one fell swoop.
			$sql = "UPDATE {$wpdb->posts} SET post_type='comic' WHERE post_type='".$name."';";
			$wpdb->query($sql);
		}
	}
	exit;
}

if (isset($_GET['clearprice']))
	add_action('template_redirect', 'ceo_clearprice');

function ceo_clearprice() {
	
	$post_args = array( 
			'showposts' => -1,
			'post_type' => 'comic',
			'order' => 'ASC'
		);					
	$qposts = get_posts( $post_args );
	foreach($qposts as $qpost) {
		delete_post_meta($qpost->ID, 'buy_print_orig_amount');
	}
}
//	exit;
*/
