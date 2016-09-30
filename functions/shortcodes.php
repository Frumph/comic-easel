<?php
/* Short Codes go Here */

add_shortcode('cast-page', 'ceo_cast_page');
add_shortcode('comic-archive', 'ceo_comic_archive_multi');
add_shortcode('transcript', 'ceo_display_transcript');
add_shortcode('buycomic', 'ceo_display_buycomic');
add_shortcode('comic-archive-dropdown', 'ceo_comic_archive_dropdown');
add_shortcode('randcomic', 'ceo_random_comic_shortcode'); // old
add_shortcode('showcomic', 'ceo_random_comic_shortcode'); // new

function ceo_cast_display($character, $stats, $image) {
	$cast_output = '';
	if ($character) {
		$cast_output .= '<tr>';
		if ($image) {
			$cast_output .= '<td class="cast-image">';
			$cast_output .= '<div class="cast-pic character-'.$character->slug.'">';
			$cast_output .= '</div></td>';
		}
		$cast_output .= '<td class="cast-info cast-info-'.$character->slug.'">';
		$cast_output .= '<h4 class="cast-name"><a href="'.get_term_link($character->slug, 'characters').'">'.$character->name.'</a></h4>';
		$cast_output .= '<p class="cast-description">';
		$cast_output .= $character->description;
		$cast_output .= '</p>';
		if ($stats) {
			$cast_output .= '<p class="cast-character-stats">';
			$cast_output .= '<i>'.__('Comics:','comiceasel').'</i> <strong>'.$character->count.'</strong><br />';
			$args = array(
					'nopaging' => true,
					'numberposts' => 9999,
					'posts_per_page'  => 9999,
					'post_type' => 'comic',
					'orderby' => 'post_date',
					'order' => 'ASC',
					'post_status' => 'publish',
					'characters' => $character->slug,
					);
			$qposts = get_posts( $args );
			if (!empty($qposts)) {
				$first_seen_object = reset($qposts);
				$first_seen_title = $first_seen_object->post_title;
				$first_seen_id = $first_seen_object->ID;
				$last_seen_object = end($qposts);
				$last_seen_title = $last_seen_object->post_title;
				$last_seen_id = $last_seen_object->ID;
				if ($first_seen_id == $last_seen_id) {
					$cast_output .= '<i>'.__('Only Appearance:','comiceasel').'</i> <a href="'.get_permalink($first_seen_id).'">'.$first_seen_title.'</a><br />';
				} else {
					$cast_output .= '<i>'.__('Recent Appearance:','comiceasel').'</i> <a href="'.get_permalink($last_seen_id).'">'.$last_seen_title.'</a><br />';
					$cast_output .= '<i>'.__('First Appearance:','comiceasel').'</i> <a href="'.get_permalink($first_seen_id).'">'.$first_seen_title.'</a><br />';			
				}
			}
			$qposts = null;
			$cast_output .= '</p>';
		}
		$cast_output .= '</td></tr>';
	}
	return $cast_output;
}

// , $limit, $stats, $image, $order
function ceo_get_character_list($chapter) {
	global $wpdb;
	$sql_string3 = "SELECT DISTINCT terms2.name as tag
			FROM
			wp_posts as p1
			LEFT JOIN wp_term_relationships as r1 ON p1.ID = r1.object_ID
			LEFT JOIN wp_term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
			LEFT JOIN wp_terms as terms1 ON t1.term_id = terms1.term_id,
			
			wp_posts as p2
			LEFT JOIN wp_term_relationships as r2 ON p2.ID = r2.object_ID
			LEFT JOIN wp_term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
			LEFT JOIN wp_terms as terms2 ON t2.term_id = terms2.term_id
			WHERE
			t1.taxonomy = 'chapters' AND p1.post_status = 'publish' AND terms1.term_id = '".$chapter."' AND
			t2.taxonomy = 'characters' AND p2.post_status = 'publish'
			AND p1.ID = p2.ID";
	
	$character_list = $wpdb->get_results($sql_string3);
	if (!empty($character_list)) return $character_list;
	return false;
}

function ceo_cast_page( $atts, $content = '' ) {
	extract( shortcode_atts( array(
					'character' => '',
					'limit' => '',
					'order' => 'desc',
					'stats' => 1,
					'image' => 1,
					'chapter' => 0
					), $atts ) );
	$cast_output = '';
	if ($chapter) {
		$character_list = ceo_get_character_list($chapter);
		$cast_output .= '<table class="cast-wrapper">'."\r\n";
		foreach ($character_list as $character) {
			$character_object = get_term_by('slug', $character->tag, 'characters');
			$cast_output .= ceo_cast_display($character_object, $stats, $image)."\r\n";
		}
		$cast_output .= '</table>'."\r\n";
		return $cast_output;
	}
	if (empty($character)) {
		if ($limit) {
			$args = 'orderby=count&order='.$order.'&hide_empty=1&number='.$limit;
		} else $args = 'orderby=count&order='.$order.'&hide_empty=1';
		$characters = get_terms( 'characters', $args );
		if (is_array($characters)) {
			$cast_output .= '<table class="cast-wrapper">'."\r\n";
			foreach ($characters as $character) {
				$cast_output .= ceo_cast_display($character, $stats, $image)."\r\n";
			}
			$cast_output .= '</table>'."\r\n";
		} else {
			$cast_output = __('You do not have any characters yet.','comiceasel')."<br />\r\n";
		}
	} else {
		$single_character = get_term_by('slug', $character, 'characters');
		if (!empty($single_character)) {
			$cast_output .= '<table class="cast-wrapper">'."\r\n";
			$cast_output .= ceo_cast_display($single_character, $stats, $image)."\r\n";
			$cast_output .= '</table>'."\r\n";
		} else 
			$cast_output .= __('Unknown Character:', 'comiceasel').'&nbsp;'.$character."<br />\r\n";
	}
	return $cast_output;
}

function ceo_comic_archive_multi(  $atts, $content = '' ) {
	extract( shortcode_atts( array(
					'list' => 0,
					'style' => 0,
					'chapter' => 0,
					'thumbnail' => 0,
					'order' => 'ASC'
					), $atts ) );
	$output = '';
	switch ($list) {
		case 4:
			$output = ceo_archive_list_by_chapter_thumbnails($order);
			break;
		case 3:
			$output = ceo_archive_list_by_all_years($thumbnail, $order, $chapter);
			break;
		case 2: 
			$output = ceo_archive_list_by_year($thumbnail, $order, $chapter);
			break;
		case 1:
			$output = ceo_archive_list_series($thumbnail);
			break;
		case 0:
		default:
			if ($chapter) {
				$output = ceo_archive_list_single($chapter, $order, $thumbnail);
			} else {
				$output = ceo_archive_list_all($order, $thumbnail);
			}
			break;
	}
	wp_reset_postdata();
	return $output;
}

function ceo_archive_list_single($chapter = 0, $order = 'ASC', $thumbnail = 0) {
	$output = '';
	// get chapter from ID#
	$single_chapter = get_term_by('term_id', $chapter, 'chapters');
	if (is_null($single_chapter)) { echo "Invalid Chapter Specified"; return; }
	$output .= '<div class="comic-archive-chapter-wrap">';
	$output .= '<h3 class="comic-archive-chapter">'.$single_chapter->name.'</h3>';
	$output .= '<div class="comic-archive-image-'.$single_chapter->slug.'"></div>';
	$output .= '<div class="comic-archive-chapter-description">'.$single_chapter->description.'</div>';
	$args = array(
			'numberposts' => -1,
			'post_type' => 'comic',
			'orderby' => 'post_date',
			'order' => $order,
			'post_status' => 'publish',
			'chapters' => $single_chapter->slug
			);					
	$qposts = get_posts( $args );
	$archive_count = 0;
	if ($thumbnail) {
		$output .= '<div class="comic-archive-thumbnail">'.get_the_post_thumbnail($qposts[0]->ID, 'thumbnail').'</div>';
	}
	$output .= '<div class="comic-archive-list-wrap">';	
	$css_alt = false;	
	foreach ($qposts as $qpost) {
		$archive_count++;
		if ($css_alt) { $alternate = ' comic-list-alt'; $css_alt = false; } else { $alternate = ''; $css_alt=true; }		
		$output .= '<div class="comic-list comic-list-'.$archive_count.$alternate.'"><span class="comic-archive-date">'.get_the_time('M d, Y', $qpost->ID).'</span><span class="comic-archive-title"><a href="'.get_permalink($qpost->ID).'" rel="bookmark" title="'.__('Permanent Link:','comiceasel').' '.$qpost->post_title.'">'.$qpost->post_title.'</a></span></div>';
	}
	$output .= '</div>';
	$output .= '<div style="clear:both;"></div></div>';
	return $output;
}

function ceo_get_terms_orderby($orderby, $args) {
	$orderby = 't.menu_order';
	return $orderby;
}

function ceo_archive_list_all($order = 'ASC', $thumbnail = 0) {
	$output = '';
	$main_args = array(
			'hide_empty' => true,
			'order' => $order,
			'orderby' => 'menu_order',
			'hierarchical' => 1
			);
	$all_chapters = get_terms('chapters', $main_args);
	if (is_null($all_chapters)) { echo 'There are no chapters available.'; return; }
	$output = '';
	foreach ($all_chapters as $chapter) {
		if ($chapter->count) {
			$output .= '<div class="comic-archive-chapter-wrap">'."\r\n";
			$output .= '<h3 class="comic-archive-chapter">'.$chapter->name.'</h3>'."\r\n";
			$output .= '<div class="comic-archive-image-'.$chapter->slug.'"></div>'."\r\n";
			$output .= '<div class="comic-archive-chapter-description">'.$chapter->description.'</div>'."\r\n";			
			$args = array(
					'numberposts' => -1,
					'post_type' => 'comic',
					'orderby' => 'post_date',
					'order' => $order,
					'post_status' => 'publish',
					'chapters' => $chapter->slug
					);					
			$qposts = get_posts( $args );
			$archive_count = 0;
			if ($thumbnail) {
				$get_thumbnail = (strtoupper($order) == 'ASC') ? get_the_post_thumbnail(reset($qposts)->ID, 'thumbnail') : get_the_post_thumbnail(end($qposts)->ID, 'thumbnail');
				$output .= '<div class="comic-archive-thumbnail">'.$get_thumbnail.'</div>'."\r\n";
			}
			$output .= '<div class="comic-archive-list-wrap">'."\r\n";
			$css_alt = false;
			foreach ($qposts as $qpost) {
				$archive_count++;
				if ($css_alt) { $alternate = ' comic-list-alt'; $css_alt = false; } else { $alternate = ''; $css_alt=true; }
				$output .= '<div class="comic-list comic-list-'.$archive_count.$alternate.'"><span class="comic-archive-date">'.get_the_time('M d, Y', $qpost->ID).'</span><span class="comic-archive-title"><a href="'.get_permalink($qpost->ID).'" rel="bookmark" title="'.__('Permanent Link:','comiceasel').' '.$qpost->post_title.'">'.$qpost->post_title.'</a></span></div>'."\r\n";
			}
			$output .= '</div>'."\r\n";
			$output .= '<div style="clear:both;"></div></div>'."\r\n";
		}
		$qposts = null;
	}
	return $output;
}

function ceo_archive_list_series($thumbnail = 0) {
	$output = '';
	$archive_count = 0;
	$args = array(
			'pad_counts' => 0,
			'order' => 'ASC',
			'hide_empty' => false,
			'parent' => 0,
			'orderby' => 'menu_order'
			);
	$parent_chapters = get_terms('chapters', $args);
	if (is_array($parent_chapters)) {
		foreach($parent_chapters as $parent_chapter) {
			$output .= '<h2 class="comic-archive-series-title">'.$parent_chapter->name.'</h2>';
			$output .= '<div class="comic-archive-image-'.$parent_chapter->slug.'"></div>';
			$output .= '<div class="comic-archive-series-description">'.$parent_chapter->description.'</div>';
			$child_chapters = get_term_children( $parent_chapter->term_id, 'chapters' );
			foreach ($child_chapters as $child) {
				$child_term = get_term_by( 'id', $child, 'chapters' );
				if ($child_term->count) {
					$output .= '<div class="comic-archive-chapter-wrap">';
					$output .= '<h3 class="comic-archive-chapter-title">'.$child_term->name.'</h3>';
					$output .= '<div class="comic-archive-image-'.$child_term->slug.'"></div>';
					$output .= '<div class="comic-archive-chapter-description">'.$child_term->description.'</div>';
					$child_args = array( 
							'numberposts' => -1, 
							'post_type' => 'comic',
							'orderby' => 'post_date', 
							'order' => 'ASC', 
							'post_status' => 'publish', 
							'chapters' => $child_term->slug 
							);					
					$qcposts = get_posts( $child_args );
					if ($thumbnail) {
						$output .= '<div class="comic-archive-thumbnail">'.get_the_post_thumbnail($qcposts[0]->ID, 'thumbnail').'</div>';
					}
					$output .= '<div class="comic-archive-list-wrap">';	
					$css_alt = false;	
					foreach ($qcposts as $qcpost) {
						$archive_count++;
						if ($css_alt) { $alternate = ' comic-list-alt'; $css_alt = false; } else { $alternate = ''; $css_alt=true; }		
						$output .= '<div class="comic-list comic-list-'.$archive_count.$alternate.'"><span class="comic-archive-date">'.get_the_time('M d, Y', $qcpost->ID).'</span><span class="comic-archive-title"><a href="'.get_permalink($qcpost->ID).'" rel="bookmark" title="'.__('Permanent Link:','comiceasel').' '.$qcpost->post_title.'">'.$qcpost->post_title.'</a></span></div>';
					}
					$output .= '</div>';
					$output .= '<div style="clear:both;"></div></div>';	
				}
			}
		}
		return $output;
	}
}

function ceo_archive_list_by_chapter_thumbnails($order = 'ASC', $showtitle = false) {
	$output = '';
	$archive_count = 0;
	$args = array(
			'pad_counts' => 0,
			'order' => $order,
			'hide_empty' => 1,
			'orderby' => 'menu_order'
			);
	$chapters = get_terms('chapters', $args);
	if (is_array($chapters) && !is_wp_error($chapters)) {
		$output .= '<div class="comic-archive-list-4">';
		foreach($chapters as $chapter) {
			$qcposts = null;
			if (!empty($chapter->menu_order)) {
				$child_args = array( 
						'numberposts' => 1, 
						'post_type' => 'comic',
						'orderby' => 'post_date', 
						'order' => 'ASC', 
						'post_status' => 'publish', 
						'chapters' => $chapter->slug 
						);					
				$qcposts = get_posts( $child_args );
				$qcposts = reset($qcposts);
				if (has_post_thumbnail($qcposts->ID)) {
					$output .= '<div class="comic-archive-thumbnail"><a href="'.get_permalink($qcposts).'">'.get_the_post_thumbnail($qcposts->ID, 'thumbnail').'</a></div>';
				} else $output .= __('No Thumbnail Found', 'comiceasel');
			}
		}
		$output .= '<div class="clear"></div></div>';
		return $output;
	}
}

function ceo_display_transcript($atts, $content = null) {
	extract( shortcode_atts( array(
					'display' => 'styled'
					), $atts ) );
	if (is_archive() || is_search() || ceo_pluginfo('enable_transcripts_in_comic_posts')) return;
	return ceo_the_transcript($display);
}

function ceo_display_the_transcript_action() {
	global $post;
	if (is_archive() || is_search() || ceo_pluginfo('enable_transcripts_in_comic_posts')) return;
	return ceo_the_transcript('styled');
}

function ceo_the_transcript($displaymode = 'raw') {
	global $post;
	$transcript = get_post_meta( $post->ID, "transcript", true );
	apply_filters('ceo_the_transcript_raw', $transcript);
	if (!empty($transcript)) {			
		switch ($displaymode) {
			case "raw":
				return $transcript;
				break;
			case "br":
				return nl2br($transcript);
				break;
			case "styled":
				$output = "<script type='text/javascript'>\r\n";
				$output .= "<!--\r\n";
				$output .= "function toggle_expander(id) {\r\n";
				$output .= "	var e = document.getElementById(id);\r\n";
				$output .= "	if(e.style.height == 'auto')\r\n";
				$output .= "		e.style.height = '1px';\r\n";
				$output .= "	else\r\n";
				$output .= "		e.style.height = 'auto';\r\n";
				$output .= "}\r\n";
				$output .= "//-->\r\n";
				$output .= "</script>\r\n";
				$output .= "<div class=\"transcript-border\"><div id=\"transcript\"><a href=\"javascript:toggle_expander('transcript-content');\" class=\"transcript-title\">&darr; Transcript</a><div id=\"transcript-content\">".nl2br($transcript)."<br /><br /></div></div></div>\r\n";
				$output .= "<script type='text/javascript'>\r\n";
				$output .= "<!--\r\n";
				$output .= "	document.getElementById('transcript-content').style.height = '1px';\r\n";
				$output .= "//-->\r\n";
				$output .= "</script>\r\n";
				return $output;
				break;
		}
	}
}

function ceo_archive_list_by_year($thumbnail = false, $order = 'ASC', $chapter = 0) {
	global $wpdb;
	if (isset($_GET['archive_year'])) { 
		$archive_year = (int)esc_attr($_GET['archive_year']); 
	} else { 
		$latest_comic = ceo_get_last_comic(false);
		$archive_year = get_post_time('Y', false, $latest_comic, true);
	}
	if (empty($archive_year)) $archive_year = date('Y');
	$output = '<h3 class="year-title">'.$archive_year.'</h3>';
	$output .= '<br />';
	$output .= '<div class="archive-yearlist">| ';
	
	if ($chapter) {
		$years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'chapters' AND $wpdb->term_taxonomy.term_id = ".$chapter." ORDER BY post_date ".$order);
	} else {
		$years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type='comic' ORDER BY post_date ASC");
	}
	foreach ( $years as $year ) {
		if ($year != (0) ) {
			$output .= '<a href="'.add_query_arg('archive_year', $year).'"><strong>'.$year.'</strong></a> | ';
		} 
	}
	$output .= '</div>';
	$output .= '<div class="clear"></div>';
	$output .= '<table class="month-table">';
	if ($chapter) {
		$comic_args = array(
				'showposts' => -1,
				'year' => (int)$archive_year,
				'post_type' => 'comic',
				'chapter' => $chapter,
				'order' => $order
				);
	} else {
		$comic_args = array(
				'showposts' => -1,
				'year' => (int)$archive_year,
				'post_type' => 'comic',
				'order' => $order
				);	
	}
	$theposts = get_posts($comic_args);
	foreach ($theposts as $post) {
		$output .= '<tr><td class="archive-date">'.get_the_time('M j', $post).'</td><td class="archive-title"><a href="'.get_permalink($post->ID).'" rel="bookmark" title="'.get_the_title($post->ID).'">'.get_the_title($post->ID).'</a></td></tr>';
	}
	$output .= '</table>';
	return $output;
}

function ceo_archive_list_by_all_years($thumbnail = false, $order = 'ASC', $chapter = 0) {
	global $wpdb;
	$latest_comic = ceo_get_last_comic(false);
	$archive_year_latest = get_post_time('Y', false, $latest_comic, true);
	$first_comic = ceo_get_first_comic(false);
	$archive_year_first = get_post_time('Y', false, $first_comic, true);
	if ($chapter) {
		$years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'chapters' AND $wpdb->term_taxonomy.term_id = ".$chapter." ORDER BY post_date ".$order);
	} else {
		$years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type='comic' ORDER BY post_date ".$order);
	}
	$output = '';
	foreach ( $years as $year ) {
		if ($chapter) {
			$comic_args = array(
					'showposts' => -1,
					'year' => (int)$year,
					'post_type' => 'comic',
					'chapter' => $chapter,
					'order' => $order
					);			
		} else {
			$comic_args = array(
					'showposts' => -1,
					'year' => (int)$year,
					'post_type' => 'comic',
					'order' => $order
					);
		}
		$theposts = get_posts($comic_args);
		$output .= '<h3 class="year-title">'.$year.'</h3>';
		$output .= '<table class="month-table">';			
		foreach ($theposts as $post) {
			$output .= '<tr><td class="archive-date">'.get_the_time('M j', $post->ID).'</td><td class="archive-title"><a href="'.get_permalink($post->ID).'" rel="bookmark" title="'.get_the_title($post->ID).'">'.get_the_title($post->ID).'</a></td></tr>';
		}
		$output .= '</table>';
	}
	return $output;
}

function ceo_display_buycomic( $atts, $content = '' ) {
	global $post;
	extract(shortcode_atts( array(
					'character' => '',
					'thanks' => __('Thank you for the purchase!','comiceasel'),
					'cancelled' => __('You have cancelled the transaction.','comiceasel')
					), $atts ) );
	$buy_output = '';
	if (isset($_REQUEST['id'])) $comicnum = intval($_REQUEST['id']);
	if (isset($_REQUEST['action'])) { 
		$action = esc_attr($_REQUEST['action']);
		switch ($action) {
			case 'thankyou':
				$buy_output .= '<div class="buycomic-thankyou">';
				$buy_output .= $thanks;
				$buy_output .= '</div>';
				break;
			case 'cancelled':
				$buy_output .= '<div class="buycomic-cancelled">';
				$buy_output .= $cancelled;
				$buy_output .= '</div>';
				break;
		}
	}
	if (isset($comicnum)) {
		
		$buy_print_orig_amount = get_post_meta($comicnum , 'buy_print_orig_amount', true);
		if (empty($buy_print_orig_amount)) $buy_print_orig_amount = ceo_pluginfo('buy_comic_orig_amount');
		
		$buy_print_amount = get_post_meta($comicnum , 'buy_print_amount', true);
		if (empty($buy_print_amount)) $buy_print_amount = ceo_pluginfo('buy_comic_print_amount');
		
		$buyprint_status = get_post_meta($comicnum , 'buyprint-status', true);
		if (empty($buyprint_status)) $buyprint_status = __('Available','comiceasel');
		
		$buyorig_status = get_post_meta($comicnum , 'buyorig-status', true);
		if (empty($buyorig_status)) $buyorig_status = __('Available','comiceasel');
		
		ceo_protect();
		$post = get_post($comicnum); // Get the post
		if (!is_wp_error($post) && !empty($post)) { // error check make sure it got a post
			$buy_output .= __('Comic ID','comiceasel').' #'.$comicnum."<br />\r\n";
			$buy_output .= __('Title:','comiceasel').'&nbsp;'.get_the_title($post)."<br />\r\n";
			if (ceo_pluginfo('buy_comic_sell_print')) {
				$buy_output .= __('Print Status:','comiceasel').'&nbsp;'.$buyprint_status."<br />\r\n";
			}
			if (ceo_pluginfo('buy_comic_sell_original')) {
				$buy_output .= __('Original Status:','comiceasel').'&nbsp;'.$buyorig_status."<br />\r\n";
			}
			$buy_output .= "<br />\r\n";
			$buy_output .= '<table class="buytable" style="width:100%;">';
			$buy_output .= '<tr>';
			// buy print
			if (ceo_pluginfo('buy_comic_sell_print')) {
				$buy_output .= '<td align="left" valign="top" style="width:50%;">';
				$buy_output .= '<div class="buycomic-us-form">';
				$buy_output .= '<h4 class="buycomic-title">Print</h4>';
				$buy_output .= '$'.$buy_print_amount.'<br />';
				if ($buyprint_status == __('Available','comiceasel')) {
					$buy_output .= '<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">';
					$buy_output .= '<input type="hidden" name="add" value="1" />';
					$buy_output .= '<input type="hidden" name="cmd" value="_cart" />';
					$buy_output .= '<input type="hidden" name="notify_url" value="'.home_url().'/?ceopaypalipn">';
					$buy_output .= '<input type="hidden" name="item_name" value="'.__('Print','comiceasel').' - '.get_the_title($post->ID).' - '.$post->ID.'" />';
					// Say a thank you and that transaction went through with an action
					$url = ceo_pluginfo('buy_comic_url');
					$url_and = (strpos($url,'?')) ? $url.'&amp;' : $url.'?';
					$buy_output .= '<input type="hidden" name="return" value="'.$url_and.'action=thankyou&amp;id='.$comicnum.'" />';
					$buy_output .= '<input type="hidden" name="amount" value="'.$buy_print_amount.'" />';
					$buy_output .= '<input type="hidden" name="item_number" value="'.$comicnum.'" />';
					$buy_output .= '<input type="hidden" name="business" value="'.ceo_pluginfo('buy_comic_email').'" />';
					$buy_output .= '<input type="image" src="'.ceo_pluginfo('plugin_url').'images/buynow_paypal.png" name="submit32" alt="'.__('Make payments with PayPal - it is fast, free and secure!','comiceasel').'" />';
					$buy_output .= '</form>';
				}
				if ($buyprint_status == __('Sold','comiceasel')) {
					$buy_output .= '<img src="'.ceo_pluginfo('plugin_url').'images/sold.png" alt="'.__('Sold','comiceasel').'" />';
				} elseif ($buyprint_status == __('Out Of Stock','comiceasel')) {
					$buy_output .= '<img src="'.ceo_pluginfo('plugin_url').'images/outofstock.png" alt="'.__('Out Of Stock','comiceasel').'" />';
				} elseif ($buyprint_status == __('Not Available','comiceasel')) { 
					$buy_output .= '<img src="'.ceo_pluginfo('plugin_url').'images/notavailable.png" alt="'.__('Not Available','comiceasel').'" />';
				}
				$buy_output .= '</div>';
				$buy_output .= '</td>';
			}
			// buy original
			if (ceo_pluginfo('buy_comic_sell_original')) {
				$buy_output .= '<td align="left" valign="top" style="width:50%;">';
				$buy_output .= '<div class="buycomic-us-form" style="width:100%;">';
				$buy_output .= '<h4 class="buycomic-title">Original</h4>';
				$buy_output .= '$'.$buy_print_orig_amount.'<br />';
				if ($buyorig_status == __('Available','comiceasel')) {
					$buy_output .= '<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">';
					$buy_output .= '<input type="hidden" name="add" value="1" />';
					$buy_output .= '<input type="hidden" name="cmd" value="_cart" />';
					$buy_output .= '<input type="hidden" name="notify_url" value="'.home_url().'/?ceopaypalipn">';
					$buy_output .= '<input type="hidden" name="item_name" value="'.__('Original','comiceasel').' - '.get_the_title($post->ID).' - '.$post->ID.'" />';
					// Say a thank you and that transaction went through with an action
					$url = ceo_pluginfo('buy_comic_url');
					$url_and = (strpos($url,'?')) ? $url.'&amp;' : $url.'?';
					$buy_output .= '<input type="hidden" name="return" value="'.$url_and.'action=thankyou&amp;id='.$comicnum.'" />';
					$buy_output .= '<input type="hidden" name="amount" value="'.$buy_print_orig_amount.'" />';
					$buy_output .= '<input type="hidden" name="item_number" value="'.$comicnum.'" />';
					$buy_output .= '<input type="hidden" name="business" value="'.ceo_pluginfo('buy_comic_email').'" />';
					$buy_output .= '<input type="image" src="'.ceo_pluginfo('plugin_url').'images/buynow_paypal.png" name="submit32" alt="'.__('Make payments with PayPal - it is fast, free and secure!','comiceasel').'" />';
					$buy_output .= '</form>';
				}
				if ($buyorig_status == __('Sold','comiceasel')) {
					$buy_output .= '<img src="'.ceo_pluginfo('plugin_url').'images/sold.png" alt="'.__('Sold','comiceasel').'" />';
				} elseif ($buyorig_status == __('Out Of Stock','comiceasel')) {
					$buy_output .= '<img src="'.ceo_pluginfo('plugin_url').'images/outofstock.png" alt="'.__('Out Of Stock','comiceasel').'" />';
				} elseif ($buyorig_status == __('Not Available','comiceasel')) { 
					$buy_output .= '<img src="'.ceo_pluginfo('plugin_url').'images/notavailable.png" alt="'.__('Not Available','comiceasel').'" />';
				}
				$buy_output .= '</div>';
				$buy_output .= '</td>';
			}
			$buy_output .= '</tr>';
			$buy_output .= "</table>\r\n";
			$buy_output .= '<div class="buy-thumbnail">';
			$buy_output .= ceo_display_comic_thumbnail('large', $post);
			$buy_output .= "</div>\r\n";
			/*			$last_info = get_option('ceo_paypal_receiver'); // Debug to see the last transaction, which is stored in this option
						if (!empty($last_info)) $buy_output .= nl2br($last_info); */
		} else {
			$buy_output .= __('Invalid Comic ID.','comiceasel')."<br />\r\n";
		}
		ceo_unprotect();
	}
	
	return $buy_output;
}

function ceo_comic_archive_dropdown($atts, $content='') {
	extract( shortcode_atts( array(
					'unhide' => false,
					'exclude' => '',
					'showcount' => false,
					'jumptoarchive' => false,
					'return' => true
					), $atts ) );
	return ceo_comic_archive_jump_to_chapter($unhide, $exclude, $showcount, $jumptoarchive, $return);
}

function ceo_random_comic_shortcode($atts, $content = '') {
	extract( shortcode_atts( array(
					'character' => '',
					'size' => 'thumbnail',
					'slug' => '',
					'chapter' => '',
					'orderby' => 'rand',
					'month' => '',
					'day' => '',
					'year' => ''
					), $atts ) );
	global $post;
	$args = array(
		'name' => $slug,
		'orderby' => $orderby,
		'showposts' => 1,
		'post_type' => 'comic',
		'chapters' => $chapter,
		'characters' => $character,
		'exclude' => $post->ID,
		'year' => $year,
		'month' => $month,
		'day' => $day
			);
	ceo_protect();
	$thumbnail_query = new WP_Query($args);
	$output = '';
	$archive_image = '';
	if ($thumbnail_query->have_posts()) {
		while ($thumbnail_query->have_posts()) : $thumbnail_query->the_post();
			$the_permalink = get_permalink($post->ID);
			$output = '<div class="rand-comic-wrap rand-comic-'.$post->ID.'">';
			if ( has_post_thumbnail($post->ID) ) {
				$output .= "<a href=\"".$the_permalink."\" rel=\"bookmark\" title=\"".get_the_title()."\">".get_the_post_thumbnail($post->ID, $size)."</a>\r\n";
			} else {
				$output .= __('No Thumbnail Found.','comiceasel');
			}
			$output .= "</div>\r\n";
		endwhile;
	}
	ceo_unprotect();	
	return $output;
}