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
			wp_redirect( bloginfo('url') );
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

function ceo_paypal_ipn() {
	$req = 'cmd=_notify-validate';
	// Get each element of IPN request
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}
	$header = '';
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
	// assign posted variables to local variables
	$num_cart_items = (isset($_POST['num_cart_items'])) ? (int)$_POST['num_cart_items'] : '1';
	$count = 1;
	$item_name = array();
	$item_number = array();
	while ($count <= $num_cart_items) {
		$item_name[$count] = (isset($_POST['item_name'.$count])) ? $_POST['item_name'.$count] : '';
		$item_number[$count] = (isset($_POST['item_number'.$count])) ? $_POST['item_number'.$count] : '';
		$count++;
	}
	
	$payment_status = (isset($_POST['payment_status'])) ? $_POST['payment_status'] : '';
	$payment_amount = (isset($_POST['mc_gross'])) ? $_POST['mc_gross'] : '';
	$payment_currency = (isset($_POST['mc_currency'])) ? $_POST['mc_currency'] : '';
	$txn_id = (isset($_POST['txn_id'])) ? $_POST['txn_id'] : '';
	$shipping = (isset($_POST['shipping'])) ? $_POST['shipping'] : '';
//	$receiver_email = (isset($_POST['receiver_email'])) ? $_POST['txn_id'] : '';
	$business = (isset($_POST['business'])) ? $_POST['business'] : '';
	$payer_email = (isset($_POST['payer_email'])) ? $_POST['payer_email'] : '';
	$first_name = (isset($_POST['first_name'])) ? $_POST['first_name'] : '';
	$last_name = (isset($_POST['last_name'])) ? $_POST['last_name'] : '';
	$address_name = (isset($_POST['address_name'])) ? $_POST['address_name'] : '';
	$address_street = (isset($_POST['address_street'])) ? $_POST['address_street'] : '';
	$address_city = (isset($_POST['address_city'])) ? $_POST['address_city'] : '';
	$address_state = (isset($_POST['address_state'])) ? $_POST['address_state'] : '';
	$address_zip = (isset($_POST['address_zip'])) ? $_POST['address_zip'] : '';
	$address_country = (isset($_POST['address_country'])) ? $_POST['address_country'] : '';
	$memo = (isset($_POST['memo'])) ? $_POST['memo'] : '';
	
	$email_message = '';
	$comiceasel_config = get_option('comiceasel-config');
	delete_option('ceo_paypal_receiver');
	if (!$fp) {
		$email_message .= "HTTP Error - Host could not connect to paypal.\r\n";
	} else {
		fputs($fp, $header . $req);
		$res = '';
		while (!feof($fp)) {
			$res .= fgets($fp, 1024);
		}
		if ($payment_status == 'Completed') {
			$comiceasel_options = get_option('comiceasel_options');
			$count = 1;
			foreach ($item_number as $item_sub_number) {
				$post_id = (int)$item_number[$count];
				if (strstr(strtolower($item_name[$count]), 'original')) {
					$post_id = (int)$item_number[$count];
					update_post_meta($post_id, 'buyorig-status', __('Sold','comiceasel'));
					$email_message .= 'Comic ID #'.$post_id." Set to SOLD\r\n\r\n";
					// Flush the cache on the item in question.
					if (defined('WP_CACHE') && WP_CACHE == true) {
						wp_cache_no_postid($post_id);
					}
				}
				$count++;
			}
		} elseif (strcmp($res,"INVALID") == 0) {
			$email_message .= "Invalid Transaction\r\n";
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
	}
	fclose ($fp);				
	exit;
}