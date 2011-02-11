<?php

function ceo_get_first_comic() {
	global $post;
	$current_chapter = reset(get_the_terms( $post->ID, 'chapters'));
	if (empty($current_chapter) || is_null($current_chapter)) { 
		$current_chapter_id = 0;
	} else {
		$current_chapter_id = $current_chapter->term_id;
	}
	return ceo_get_terminal_post_of_chapter($current_chapter_id, true);
}

function ceo_get_first_comic_permalink() {
	$terminal = ceo_get_first_comic();
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function ceo_get_last_comic() {
	global $post;
	$current_chapter = reset(get_the_terms( $post->ID, 'chapters'));
	if (empty($current_chapter) || is_null($current_chapter)) { 
		$current_chapter_id = 0;
	} else {
		$current_chapter_id = $current_chapter->term_id;
	}	
	return ceo_get_terminal_post_of_chapter($current_chapter_id, false);
}

function ceo_get_last_comic_permalink() {
	$terminal = ceo_get_last_comic();
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function ceo_get_previous_comic() {
	return ceo_get_adjacent_comic(true, true);
}

function ceo_get_previous_comic_permalink() {
	$prev_comic = ceo_get_previous_comic();
	if (is_object($prev_comic)) {
		if (isset($prev_comic->ID)) {
			return get_permalink($prev_comic->ID);
		}
	}
	return false;
}

function ceo_get_next_comic() {
	return ceo_get_adjacent_comic(false, true);
}

function ceo_get_next_comic_permalink() {
	$next_comic = ceo_get_next_comic();
	if (is_object($next_comic)) {
		if (isset($next_comic->ID)) {
			return get_permalink($next_comic->ID);
		}
	}
	return false;
}


// 0 means get the first of them all, no matter chapter, otherwise 0 = this chapter.
function ceo_get_terminal_post_of_chapter($chapterID = 0, $first = true) {
	
	$sortOrder = $first ? "asc" : "desc";	
	
	if (!empty($chapterID)) {
		$chapter = &get_term_by('id', $chapterID, 'chapters');
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

	$join  = apply_filters( "get_{$adjacent}_{$taxonomy}_join", $join, $in_same_chapter, $excluded_chapters );
	$where = apply_filters( "get_{$adjacent}_{$taxonomy}_where", $wpdb->prepare("WHERE p.post_date $op %s AND p.post_type = %s AND p.post_status = 'publish' $posts_in_ex_cats_sql", $current_post_date, $post->post_type), $in_same_chapter, $excluded_chapters );
	$sort  = apply_filters( "get_{$adjacent}_{$taxonomy}_sort", "ORDER BY p.post_date $order LIMIT 1" );

	$query = "SELECT p.* FROM $wpdb->posts AS p $join $where $sort";
	$query_key = "adjacent_{$taxonomy}_" . md5($query);
	$result = wp_cache_get($query_key, 'counts');
	if ( false !== $result )
		return $result;

	$result = $wpdb->get_row("SELECT p.* FROM $wpdb->posts AS p $join $where $sort");
	if ( null === $result )
		$result = '';

	wp_cache_set($query_key, $result, 'counts');
	return $result;
}

?>
