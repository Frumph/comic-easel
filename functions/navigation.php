<?php

// TODO these can be combined, there's no need to have them separate functions
// we're not going to have any code anywhere that needs the object itself.
function ceo_get_first_comic() {
	return ceo_get_terminal_post_of_chapter(0, true);
}

function ceo_get_first_comic_permalink() {
	$terminal = ceo_get_first_comic();
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function ceo_get_last_comic() {
	return ceo_get_terminal_post_of_chapter(0, false);
}

function ceo_get_last_comic_permalink() {
	$terminal = ceo_get_last_comic();
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function ceo_get_previous_comic() {
	return ceo_get_adjacent_comic(false, true);
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
	return ceo_get_adjacent_comic(false, false);
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

function ceo_get_current_chapter_start_permalink() {
	// Get the first chapter on lists ID or slug
	// ceo_get_terminal_post_of_chapter($chapterid, true);
}

function ceo_get_current_chapter_end_permalink() {
	// Get the first chapter on lists ID or slug
	// ceo_get_terminal_post_of_chapter($chapterid, false);
}

function ceo_get_previous_chapter_start() {
	// we're just going to go by sort order.
}

function ceo_get_previous_chapter_start_permalink() {
}

function ceo_get_next_chapter_start() {
	// going by a particular sort order, have to look them up
}

function ceo_get_next_chapter_start_permalink() {
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
function ceo_get_adjacent_comic($in_same_chapter = false, $previous = true, $excluded_chapters = '', $taxonomy = 'comic') {
	global $post, $wpdb;

	if ( empty( $post ) )
		return null;

	$current_post_date = $post->post_date;

	$join = '';
	$posts_in_ex_cats_sql = '';
	if ( $in_same_chapter || !empty($excluded_chapters) ) {
		$join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";

		if ( $in_same_chapter ) {
			$cat_array = wp_get_object_terms($post->ID, $taxonomy, array('fields' => 'ids'));
			$join .= " AND tt.taxonomy = '".$taxonomy."' AND tt.term_id IN (" . implode(',', $cat_array) . ")";
		}

		$posts_in_ex_cats_sql = "AND tt.taxonomy = '".$taxonomy."'";
		if ( !empty($excluded_chapters) ) {
			$excluded_chapters = array_map('intval', explode(' and ', $excluded_chapters));
			if ( !empty($cat_array) ) {
				$excluded_chapters = array_diff($excluded_chapters, $cat_array);
				$posts_in_ex_cats_sql = '';
			}

			if ( !empty($excluded_chapters) ) {
				$posts_in_ex_cats_sql = " AND tt.taxonomy = '".$taxonomy."' AND tt.term_id NOT IN (" . implode($excluded_chapters, ',') . ')';
			}
		}
	}

	$adjacent = $previous ? 'previous' : 'next';
	$op = $previous ? '<' : '>';
	$order = $previous ? 'DESC' : 'ASC';

	$join  = apply_filters( "get_{$adjacent}_comic_join", $join, $in_same_chapter, $excluded_chapters );
	$where = apply_filters( "get_{$adjacent}_comic_where", $wpdb->prepare("WHERE p.post_date $op %s AND p.post_type = %s AND p.post_status = 'publish' $posts_in_ex_cats_sql", $current_post_date, $post->post_type), $in_same_cat, $excluded_categories );
	$sort  = apply_filters( "get_{$adjacent}_comic_sort", "ORDER BY p.post_date $order LIMIT 1" );

	$query = "SELECT p.* FROM $wpdb->posts AS p $join $where $sort";
	$query_key = 'adjacent_comic_' . md5($query);
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
