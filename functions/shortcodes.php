<?php
/* Short Codes go Here */

add_shortcode('cast-page', 'ceo_cast_page');
add_shortcode('comic-archive', 'ceo_comic_archive_multi');
add_shortcode('transcript', 'ceo_display_transcript');

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

function ceo_cast_page( $atts, $content = '' ) {
	extract( shortcode_atts( array(
					'character' => '',
					'limit' => '',
					'order' => 'desc',
					'stats' => 1,
					'image' => 1
					), $atts ) );
	$cast_output = '';
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
			$cast_output .= __('Unknown Character: ', 'comiceasel').$character."<br />\r\n";
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

function ceo_archive_list_all($order = 'ASC', $thumbnail = 0) {
	$output = '';
	$main_args = array(
			'hide_empty' => 1,
			'orderby' => 'menu_order',
			'order' => $order
			);
	$all_chapters = get_terms('chapters', $main_args);
	if (is_null($all_chapters)) { echo 'There are no chapters available.'; return; }
	$output = '';
	foreach ($all_chapters as $chapter) {
		if ($chapter->count) {
			$output .= '<div class="comic-archive-chapter-wrap">';
			$output .= '<h3 class="comic-archive-chapter">'.$chapter->name.'</h3>';
			$output .= '<div class="comic-archive-image-'.$chapter->slug.'"></div>';
			$output .= '<div class="comic-archive-chapter-description">'.$chapter->description.'</div>';			
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
		}
	}
	return $output;
}

function ceo_archive_list_series($thumbnail = 0) {
	$output = '';
	$archive_count = 0;
	$args = array(
			'pad_counts' => 0,
			'orderby' => 'menu_order',
			'order' => 'DESC',
			'hide_empty' => 0,
			'parent' => 0
			);
	$parent_chapters = get_terms( 'chapters', $args );
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

function ceo_display_transcript($atts, $content = null) {
	extract( shortcode_atts( array(
					'display' => 'styled'
					), $atts ) );
	if (ceo_pluginfo('enable_transcripts_in_comic_posts')) return;
	return ceo_the_transcript($display);
}

function ceo_display_the_transcript_action() {
	if (ceo_pluginfo('enable_transcripts_in_comic_posts')) return;
	return ceo_the_transcript('styled');
}

function ceo_the_transcript($displaymode = 'raw') {
	global $post;
	$transcript = get_post_meta( $post->ID, "transcript", true );
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
