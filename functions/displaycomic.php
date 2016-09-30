<?php

function ceo_display_featured_image_comic($size = 'full') {
	global $post;
	$output = '';
	$usemap = '';
	$next_comic = '';
	$comic_has_map = false;
	// Allow the size to be filtered for external plugins and the like to change it on whim.
	$size = apply_filters('ceo_display_featured_image_comic_size', $size);
	$post_image_id = get_post_thumbnail_id($post->ID);
	if ($post_image_id) { // If there's a featured image.
		$hovertext = ceo_the_hovertext();
		if (!empty($hovertext)) {
			$hovertext = 'alt="'.$hovertext.'" title="'.$hovertext.'" ';
		} else {
			$hovertext = 'alt="'.get_the_title($post->ID).'" title="'.get_the_title($post->ID).'" ';
		}
		$thumbnail = wp_get_attachment_image_src( $post_image_id, $size, false);
		if (is_array($thumbnail)) {
			$thumbnail = reset($thumbnail);
			
			$comic_has_map = get_post_meta($post->ID, 'comic-has-map', true);
			if (!$comic_has_map || is_wp_error($comic_has_map)) $comic_has_map = false;
			
			$comic_lightbox = get_post_meta($post->ID, 'comic-open-lightbox', true);
			if (is_wp_error($comic_lightbox)) $comic_lightbox = false;
			
			if (ceo_pluginfo('navigate_only_chapters')) {
				$next_comic = ceo_get_next_comic_in_chapter_permalink();
			} else {
				$next_comic = ceo_get_next_comic_permalink();
			}
			
			$linkto = '';
			$linkto = get_post_meta($post->ID, 'link-to', true);
			if ($linkto) $next_comic = esc_url($linkto);
			
			if ($linkto && !$comic_has_map) $output .= '<a href="'.$linkto.'" '.$hovertext.'>';
			
			if ($comic_lightbox && !$linkto && !$comic_has_map) {
				$output .= '<a href="'.$thumbnail.'" '.$hovertext.' rel="lightbox">';
			}
			
			if (ceo_pluginfo('click_comic_next') && !empty($next_comic) && !$comic_lightbox && !$linkto && !$comic_has_map) {
				$output .= '<a href="'.$next_comic.'" '.$hovertext.'>';
			}
			// only show if the comic is not linkable
			if ($comic_has_map) $usemap = 'usemap="#comicmap" ';
			
			$output .= '<img src="'.$thumbnail.'" '.$hovertext.' '.$usemap.' />';
			if ((ceo_pluginfo('click_comic_next') && !empty($next_comic) && !$comic_has_map) || $comic_lightbox || $linkto) {
				$output .= '</a>';
			}
//			if ($comic_lightbox) $output .= '<div class="comic-lightbox-text">'.__('Click comic to view larger version.','comiceasel').'</div>';
		}
	}
	return apply_filters('ceo_display_featured_image_comic', $output);
}

function ceo_display_comic_gallery($size = 'full') {
	global $post;
	$output = '';
	if (ceo_pluginfo('click_comic_next')) {
		if (ceo_pluginfo('navigate_only_chapters')) {
			$next_comic = ceo_get_next_comic_in_chapter_permalink();
		} else {
			$next_comic = ceo_get_next_comic_permalink();
		}
	}
	$hovertext = ceo_the_hovertext();
	$comic_galleries_full = get_post_meta( $post->ID, 'comic-gallery-full', true );
	if ($comic_galleries_full) {
		$comic_lightbox = get_post_meta( $post->ID, 'comic-open-lightbox', true );
		$comic_galleries_jquery = get_post_meta( $post->ID, 'comic-gallery-jquery', true );
		if ($images = get_posts(array(
						'post_parent'    => $post->ID,
						'post_type'      => 'attachment',
						'numberposts'    => -1, // show all
						'post_status'    => null,
						'post_mime_type' => 'image',
						'orderby'        => 'menu_order',
						'order'           => 'ASC'
						))) {
			$count = 0;
			if ($comic_galleries_jquery) wp_enqueue_script('multicomic', ceo_pluginfo('plugin_url') . 'js/multicomic.js', null, null, true);
			foreach($images as $image) {
				if ($comic_galleries_jquery) $output .= '<div id="comic-'.$count.'" class="comicpane">';
				$thumbnail   = wp_get_attachment_image_src($image->ID, 'full');
				$thumbnail = reset($thumbnail);

//				$thumbnail = apply_filters('jetpack_photon_url', $thumbnail);

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

				if ($comic_galleries_jquery) $output .= "</div>\r\n";
				$count += 1;
			}
			if ($comic_galleries_jquery) $output .= "<button id=\"show-".$count."\" type=\"button\" style=\"display:none;\">".$count."</button>\r\n";
//			if ($comic_lightbox) $output .= '<div class="comic-lightbox-text">'.__('Click comic to view larger version.','comiceasel').'</div>';
		}			
	} else {
		$output .= ceo_display_featured_image_comic($size);
		$columns = get_post_meta( $post->ID, 'comic-gallery-columns', true );
		if (empty($columns)) $columns = 5;
		$args = array(
				'id'         => $post->ID,
				'columns'    => $columns,
				'exclude'    => array($post->ID)
				);
		$output .= gallery_shortcode($args);
	}	
	return apply_filters('ceo_display_comic_gallery', $output);
}

function ceo_display_flash_comic($post, $flash_url) {
	$height = get_post_meta( $post->ID, "flash_height", true );
	$width = get_post_meta( $post->ID, "flash_width", true );
	if (empty($height)) $height = '380';
	if (empty($width)) $width = '520';
	$output = '';
	$output .= '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="'.$width.'" height="'.$height.'" id="flash_comic" align="middle">'."\r\n";
	$output .= '	<param name="movie" value="'.$flash_url.'"/>'."\r\n";
	$output .= '    <!--[if !IE]>-->'."\r\n";
	$output .= '    <object type="application/x-shockwave-flash" data="'.$flash_url.'" width="'.$width.'" height="'.$height.'">'."\r\n";
	$output .= '        <param name="movie" value="'.$flash_url.'"/>'."\r\n";
	$output .= '    <!--<![endif]-->'."\r\n";
	$output .= ceo_display_featured_image_comic('full');
	$output .= '        <a href="http://www.adobe.com/go/getflash">'."\r\n";
	$output .= '            <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player"/>'."\r\n";
	$output .= '        </a>'."\r\n";
	$output .= '    <!--[if !IE]>-->'."\r\n";
	$output .= '    </object>'."\r\n";
	$output .= '    <!--<![endif]-->'."\r\n";
	$output .= '</object>';
	add_action('wp_footer', 'ceo_init_comic_swf');
	return apply_filters('ceo_display_flash_comic', $output);
}

function ceo_init_comic_swf() {
	wp_enqueue_script('swfobject', '', array(), false, true);
}

function ceo_display_comic($size = 'full') {
	global $post;
    if ( post_password_required() ) { 
		return __('This comic is password protected.','comiceasel');
    }
    $refer_only = get_post_meta( $post->ID, 'refer-only', 'true');

	if (!empty($refer_only) && !defined('CEO_DISABLE_REFER_ONLY')) {
		$ref_only_msg = '';
		$refer_only_msg = get_post_meta( $post->ID, 'refer-only-msg', 'true') ? get_post_meta( $post->ID, 'refer-only-msg', 'true') : __('Read post message below to find out how to view this.', 'comiceasel');
		if (ceo_get_referer() !== $refer_only) {
				return apply_filters('ceo_refer_only_msg', $refer_only_msg);
		}
	}
	$output = '';
	if (ceo_the_above_html()) $output .= html_entity_decode(ceo_the_above_html())."\r\n";

	if ($flash_file = get_post_meta($post->ID, "flash_file", true)) {
		$output .= ceo_display_flash_comic($post, $flash_file);
	} elseif (($media_url = get_post_meta( $post->ID, 'media_url', true )) && !defined('CEO_FEATURE_MEDIA_EMBED')) {
		$output .= '<center>';
		global $content_width;
		$old_content_width = $content_width;
		$media_width = get_post_meta($post->ID, 'media_width', true);
		if (!empty($media_width)) $content_width = $media_width;
		$output .= wp_oembed_get( $media_url );
		$content_width=$old_content_width;
		$output .= '</center>';
	} else {
		$comic_galleries = get_post_meta( $post->ID, 'comic-gallery', true );
		if ($comic_galleries) {
			$output .= ceo_display_comic_gallery($size);
		} else {
			$output .= ceo_display_featured_image_comic($size);
		}
	}	
	if (ceo_the_below_html()) $output .= html_entity_decode(ceo_the_below_html())."\r\n";
	if ($output) { 
		return apply_filters('ceo_comics_display_comic', $output);
	} else
		return apply_filters('ceo_comics_display_comic', __('<!-- No HTML, Gallery or Featured Image Found. //-->', 'comiceasel'));
}

add_filter('ceo_comics_display_comic', 'ceo_filter_comic_output',10,1);

function ceo_filter_comic_output($output = '') {
	global $post;
	return $output;
}

function ceo_the_hovertext($override_post = null) {
	global $post;
	$post_to_use = !is_null($override_post) ? $override_post : $post;
	$hovertext = esc_attr( get_post_meta( $post_to_use->ID, 'comic-hovertext', true ) );
	if (empty($hovertext)) $hovertext = esc_attr( get_post_meta($post_to_use->ID, 'hovertext', true) ); // check if using old hovertext
//	return (empty($hovertext)) ? get_the_title($post_to_use->ID) : $hovertext;
	return (empty($hovertext)) ? '' : $hovertext;
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

// Do the thumbnail display functions here.
function ceo_display_comic_thumbnail($thumbnail_size = 'thumbnail', $override_post = null, $size = array()) {
	global $post;
	$thumbnail = $output = '';
	$post_to_use = !empty($override_post) ? $override_post : $post;
	if (class_exists('MultiPostThumbnails') && ($thumbnail_size == 'secondary-image') && is_null($override_post)) {
		$thumbnail = MultiPostThumbnails::get_the_post_thumbnail(get_post_type(), 'secondary-image');
	} else {
		if (!empty($size)) {
			$thumbnail = get_the_post_thumbnail($post_to_use->ID, $size);
		} else 
			$thumbnail = get_the_post_thumbnail($post_to_use->ID, $thumbnail_size);
	}
	if ( has_post_thumbnail($post_to_use->ID) ) {
		$output =  '<a href="'.get_permalink($post_to_use->ID).'" rel="bookmark" title="'.get_the_title().'">'.$thumbnail.'</a>'."\r\n";
	} else {
//			$output = "No Thumbnail Found.";
	}
	return apply_filters('easel_display_comic_thumbnail', $output);
}
