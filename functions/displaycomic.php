<?php

function ceo_display_comic($size = 'full') {
	global $post;
    if ( post_password_required() ) { 
		return __('This information is password protected.','comiceasel');
    }
	$output = '';
	if (ceo_the_above_html()) $output .= html_entity_decode(ceo_the_above_html())."\r\n";
	$post_image_id = get_post_thumbnail_id($post->ID);
	if ($post_image_id) {
		$thumbnail = wp_get_attachment_image_src( $post_image_id, $size, false);
		if (is_array($thumbnail)) {
			$thumbnail = reset($thumbnail);
			$hovertext = ceo_the_hovertext();
			$comic_lightbox = get_post_meta( $post->ID, 'comic-open-lightbox', true );
			if (ceo_pluginfo('navigate_only_chapters')) {
				$next_comic = ceo_get_next_comic_in_chapter_permalink();
			} else {
				$next_comic = ceo_get_next_comic_permalink();
			}
			if ($comic_lightbox) {
				$output .= '<a href="'.$thumbnail.'" title="'.$hovertext.'" rel="lightbox">';
			}
			if (ceo_pluginfo('click_comic_next') && !empty($next_comic) && !$comic_lightbox) {
				$output .= '<a href="'.$next_comic.'" title="'.$hovertext.'">';
			}
			$output .= '<img src="'.$thumbnail.'" alt="'.$hovertext.'" title="'.$hovertext.'" />';
			if ((ceo_pluginfo('click_comic_next') && !empty($next_comic)) || $comic_lightbox) {
				$output .= '</a>';
			}
			if ($comic_lightbox) $output .= '<div class="comic-lightbox-text">'.__('Click comic to view larger version.','comiceasel').'</div>';
		}
	}
	if (ceo_the_below_html()) $output .= html_entity_decode(ceo_the_below_html())."\r\n";
	$comic_galleries = get_post_meta( $post->ID, 'comic-gallery', true );
	if (!empty($comic_galleries)) {
		$columns = get_post_meta( $post->ID, 'comic-gallery-columns', true );
		if (empty($columns)) $columns = 5;
		$args = array(
				'id'         => $post->ID,
				'columns'    => $columns,
				'exclude'    => $post_image_id
				);
		$output .= gallery_shortcode($args);
	}
	if ($output) { 
		return apply_filters('ceo_comics_display_comic', $output);
	} else
		return __('<!-- No HTML, Gallery or featured image Found. //-->', 'comiceasel');
}

function ceo_the_hovertext($override_post = null) {
	global $post;
	$post_to_use = !is_null($override_post) ? $override_post : $post;
	$hovertext = esc_attr( get_post_meta( $post_to_use->ID, 'comic-hovertext', true ) );
	if (empty($hovertext)) $hovertext = esc_attr( get_post_meta($post_to_use->ID, 'hovertext', true) ); // check if using old hovertext
	return (empty($hovertext)) ? get_the_title($post_to_use->ID) : $hovertext;
}

function ceo_the_above_html($override_post = null) {
	global $post;
	$post_to_use = !is_null($override_post) ? $override_post : $post;
	$html_to_use = get_post_meta( $post_to_use->ID, 'comic-html-above', true);
	return $html_to_use;
}

function ceo_the_below_html($override_post = null) {
	global $post;
	$post_to_use = !is_null($override_post) ? $override_post : $post;
	$html_to_use = get_post_meta( $post_to_use->ID, 'comic-html-below', true);
	return $html_to_use;
}

// We use this type of query so that $post is set, it's already set with is_single - but needs to be set on the home page
function ceo_display_comic_area() {
	global $wp_query, $post;
	if (is_single()) {
		ceo_display_comic_wrapper();
	} else {
		if ((is_home() || is_front_page()) && !is_paged() && !ceo_pluginfo('disable_comic_on_home_page'))  {
			ceo_Protect();
			$order = (ceo_pluginfo('display_first_comic_on_home_page')) ?  'asc' : 'desc';
			$comic_args = array(
					'showposts' => 1,
					'posts_per_page' => 1,
					'post_type' => 'comic',
					'order' => $order
					);
			$wp_query->in_the_loop = true; $comicFrontpage = new WP_Query(); $comicFrontpage->query($comic_args);
			while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
				ceo_display_comic_wrapper();
				break;
			endwhile;
			ceo_UnProtect();
		}
	}
}

// Do the thumbnail display functions here.
function ceo_display_comic_thumbnail($thumbnail_size = 'thumbnail', $override_post = null) {
	global $post;
	$thumbnail = $output = '';
	$post_to_use = !empty($override_post) ? $override_post : $post;
	
	if ( has_post_thumbnail($post_to_use->ID) ) {
		$output =  '<a href="'.get_permalink($post_to_use->ID).'" rel="bookmark" title="'.get_the_title().'">'.get_the_post_thumbnail($post_to_use->ID, $thumbnail_size).'</a>'."\r\n";
	} else {
//			$output = "No Thumbnail Found.";
	}
	return apply_filters('easel_display_comic_thumbnail', $output);
}
