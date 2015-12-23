<?php

function ceo_get_first_comic($in_chapter = false) {
	global $post;
	$current_chapter = get_the_terms( $post->ID, 'chapters');
	$current_chapter_id = 0;
	if (is_array($current_chapter) && $in_chapter) {
		$current_chapter = reset($current_chapter);
		if (ceo_pluginfo('enable_prevnext_chapter_traversing')) {
			$parent_chapter = get_term( $current_chapter->parent, 'chapters' );
			if (!empty($parent_chapter) && !is_wp_error($parent_chapter)) $current_chapter = $parent_chapter;
		}
		$current_chapter_id = $current_chapter->term_id;
	}
	return ceo_get_terminal_post_of_chapter($current_chapter_id, true);
}

function ceo_get_first_comic_permalink() {
	$terminal = ceo_get_first_comic(false);
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function ceo_get_first_comic_in_chapter_permalink() {
	$terminal = ceo_get_first_comic(true);
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function ceo_get_last_comic($in_chapter = false) {
	global $post;
	$current_chapter = get_the_terms( $post->ID, 'chapters');
	$current_chapter_id = 0;
	if (is_array($current_chapter) && $in_chapter) {
		$current_chapter = reset($current_chapter); 
		if (ceo_pluginfo('enable_prevnext_chapter_traversing')) {
			$parent_chapter = get_term( $current_chapter->parent, 'chapters' );
			if (!empty($parent_chapter) && !is_wp_error($parent_chapter)) $current_chapter = $parent_chapter;
		}
		$current_chapter_id = $current_chapter->term_id;
	}	
	return ceo_get_terminal_post_of_chapter($current_chapter_id, false);
}

function ceo_get_last_comic_permalink() {
	$terminal = ceo_get_last_comic(false);
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function ceo_get_last_comic_in_chapter_permalink() {
	$terminal = ceo_get_last_comic(true);
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function ceo_get_previous_comic($in_chapter = false) {
	return ceo_get_adjacent_comic(true, $in_chapter);
}

function ceo_get_previous_comic_permalink() {
	$prev_comic = ceo_get_previous_comic(false);
	if (is_object($prev_comic) && isset($prev_comic->ID)) {
		return get_permalink($prev_comic->ID);
	}
	return false;
}


function ceo_get_previous_comic_in_chapter_permalink() {
	$prev_comic = ceo_get_previous_comic(true);
	if (is_object($prev_comic) && isset($prev_comic->ID)) {
		return get_permalink($prev_comic->ID);
	}
	//	Go to last comic of previous chapter if possible.
	if (ceo_pluginfo('enable_prevnext_chapter_traversing')) {
		$chapter = ceo_get_adjacent_chapter(true);
		if (is_object($chapter)) {
			$terminal = ceo_get_terminal_post_of_chapter($chapter->term_id, false);
			return !empty($terminal) ? get_permalink($terminal->ID) : false;
		}
	}
	return false;
}

function ceo_get_next_comic($in_chapter = false) {
	return ceo_get_adjacent_comic(false, $in_chapter);
}

function ceo_get_next_comic_permalink() {
	$next_comic = ceo_get_next_comic(false);
	if (is_object($next_comic) && isset($next_comic->ID)) {
		return get_permalink($next_comic->ID);
	}
	return false;
}

function ceo_get_next_comic_in_chapter_permalink() {
	$next_comic = ceo_get_next_comic(true);
	if (is_object($next_comic) && isset($next_comic->ID)) {
		return get_permalink($next_comic->ID);
	}
	// go to first comic of next chapter if possible
	if (ceo_pluginfo('enable_prevnext_chapter_traversing')) {
		$chapter = ceo_get_adjacent_chapter(false);
		if (is_object($chapter)) {
			$terminal = ceo_get_terminal_post_of_chapter($chapter->term_id, true);
			return !empty($terminal) ? get_permalink($terminal->ID) : false;
		}
	}
	return false;
}


// 0 means get the first of them all, no matter chapter, otherwise 0 = this chapter.
function ceo_get_terminal_post_of_chapter($chapterID = 0, $first = true) {
	
	$sortOrder = $first ? "asc" : "desc";	
	
	if (!empty($chapterID)) {
		$chapter = get_term_by('id', $chapterID, 'chapters');
		$chapter_slug = $chapter->slug;
		$args = array(
				'chapters' => $chapter_slug,
				'order' => $sortOrder,
				'posts_per_page' => 1,
				'post_type' => 'comic'
				);
	} else {
		$args = array(
				'order' => $sortOrder,
				'posts_per_page' => 1,
				'post_type' => 'comic'
				);
	}
	
	$terminalComicQuery = new WP_Query($args);
	
	$terminalPost = false;
	if ($terminalComicQuery->have_posts()) {
		$terminalPost = reset($terminalComicQuery->posts);
	}
	return $terminalPost;
}

/**
 * Retrieve adjacent post link.
 *
 * Can either be next or previous post link.
 */
function ceo_get_adjacent_comic($previous = true, $in_same_chapter = false, $taxonomy = 'comic') {
	global $post, $wpdb;
	if ( empty( $post ) ) return null;

	$current_post_date = $post->post_date;

	$join = '';

	if ( $in_same_chapter ) {
		$join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";

		if ( $in_same_chapter ) {
			$chapt_array = wp_get_object_terms($post->ID, 'chapters', array('fields' => 'ids'));
			if (!empty($chapt_array))
				$join .= " AND tt.taxonomy = 'chapters' AND tt.term_id IN (" . implode(',', $chapt_array) . ")";
		}
	}

	$adjacent = $previous ? 'previous' : 'next';
	$op = $previous ? '<' : '>';
	$order = $previous ? 'DESC' : 'ASC';

	$where = apply_filters( "get_{$adjacent}_{$taxonomy}_where", $wpdb->prepare("WHERE p.post_date $op %s AND p.post_type = %s AND p.post_status = 'publish'", $current_post_date, $post->post_type), $in_same_chapter);
	$sort  = apply_filters( "get_{$adjacent}_{$taxonomy}_sort", "ORDER BY p.post_date $order LIMIT 1" );

	$query = "SELECT p.* FROM $wpdb->posts AS p $join $where $sort";
	$query_key = "adjacent_{$taxonomy}_{$post->ID}_{$previous}_{$in_same_chapter}"; // . md5($query);
	$result = wp_cache_get($query_key, 'counts');
	if ( false !== $result )
		return $result;

	$result = $wpdb->get_row("SELECT p.* FROM $wpdb->posts AS p $join $where $sort");
	if ( null === $result )
		$result = '';
	wp_cache_set($query_key, $result, 'counts');
	return $result;
}

function ceo_get_adjacent_chapter($prev = false) {
	global $post;
	
	$current_chapter = get_the_terms($post->ID, 'chapters');
	if (is_array($current_chapter)) { $current_chapter = reset($current_chapter); } else { return; }
	
	// cache the calculation of the desired chapter to work around a weird intermittent bug with w3 total cache's object cache
	$current_order = wp_cache_get( 'ceo_current_order_'.$current_chapter->slug );
	if ( false === $current_order ) {
		$current_order = $current_chapter->menu_order;
		wp_cache_set( 'ceo_current_order_'.$current_chapter->slug, $current_order );
	} 
	
	$find_order = (bool)$prev ? $current_order - 1 : $current_order + 1;
	
	if (!$find_order) return false;
	$args = array(
			'orderby' => 'menu_order',
			'order' => 'DESC',
			'hide_empty' => 1,
			'menu_order' => $find_order
			);
	$all_chapters = get_terms( 'chapters', $args );
	if (!is_null($all_chapters)) {
		foreach($all_chapters as $chapter) {
			if ((int)$chapter->menu_order == $find_order) return $chapter;
		}
	}
	return false;
}


function ceo_get_previous_chapter() {
	$chapter = ceo_get_adjacent_chapter(true);
	if (is_object($chapter)) {
		$child_args = array( 
				'numberposts' => 1, 
				'post_type' => 'comic',
				'orderby' => 'post_date', 
				'order' => 'ASC', 
				'post_status' => 'publish', 
				'chapters' => $chapter->slug 
				);				
		$qcposts = get_posts( $child_args );
		if (is_array($qcposts)) {
			$qcposts = reset($qcposts);
			return get_permalink($qcposts->ID);
		}
	}
	return false;
}

function ceo_get_next_chapter() {
	$chapter = ceo_get_adjacent_chapter(false);
	if (is_object($chapter)) {
		$child_args = array( 
				'numberposts' => 1, 
				'post_type' => 'comic',
				'orderby' => 'post_date', 
				'order' => 'ASC', 
				'post_status' => 'publish', 
				'chapters' => $chapter->slug 
				);				
		$qcposts = get_posts( $child_args );
		if (is_array($qcposts)) {
			$qcposts = reset($qcposts);
			return get_permalink($qcposts->ID);
		}
	}
	return false;
}
