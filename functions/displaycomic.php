<?php

// If any errors occur while searching for a comic file, the error messages will be pushed into here.
$comic_pathfinding_errors = array();

// If ComicPress Manager is installed, use the date format defined there. If not, default to
// Y-m-d.. It's best to use CPM's date definition for improved operability between theme and plugin.

if (defined("CPM_DATE_FORMAT")) {
	define("CP_DATE_FORMAT", CPM_DATE_FORMAT);
} else {
	define("CP_DATE_FORMAT", "Y-m-d");
}

// If you want to run multiple comics on a single day, define your additional filters here.
// Example: you want to run an additional comic with the filename 2008-01-01-a-my-new-years-comic.jpg.
// Define an additional filter in the list below:
//
// $comic_filename_filters['secondary'] = "{date}-a*.*";
//
// Then show the second comic on your page by calling the_comic with your filter name (PHP tags munged
// to maintain valid file syntax):
//
// < ?php the_comic('secondary'); ? >
//
// Note that it's quite possible to slurp up the wrong file if your expressions are too broad.

$comic_filename_filters = array();
$comic_filename_filters['default'] = "{date}*.*";

// TODO: Change this to $output at some point.
if (!function_exists('ceo_display_comic_navigation')) {
	function ceo_display_comic_navigation() {
		global $post, $wp_query;
		if (!ceo_pluginfo('disable_default_comic_nav')) { 
			$first_comic = ceo_get_first_comic_permalink();
			$first_text = __('&lsaquo;&lsaquo; First','comicpress');
			$last_comic = ceo_get_last_comic_permalink();
			$last_text = __('Last &rsaquo;&rsaquo;','comicpress'); 
			$next_comic = ceo_get_next_comic_permalink();
			$next_text = __('Next &rsaquo;','comicpress');
			$prev_comic = ceo_get_previous_comic_permalink();
			$prev_text = __('&lsaquo; Prev','comicpress');
?>
		<div class="nav">
			<div class="nav-first"><?php if ( get_permalink() != $first_comic ) { ?><a href="<?php echo $first_comic ?>"><?php echo $first_text; ?></a><?php } else { echo $first_text; } ?></div>
			<div class="nav-previous"><?php if ($prev_comic) { ?><a href="<?php echo $prev_comic ?>"><?php echo $prev_text; ?></a><?php } else { echo $prev_text; } ?></div>
			<div class="nav-last"><?php if ( get_permalink() != $last_comic ) { ?><a href="<?php echo $last_comic ?>"><?php echo $last_text; ?></a><?php } else { echo $last_text; } ?></div>
			<div class="nav-next"><?php if ($next_comic) { ?><a href="<?php echo $next_comic ?>"><?php echo $next_text; ?></a><?php } else { echo $next_text; } ?></div>
			<div class="clear"></div>
		</div>
<?php
		} 
	}
}

function ceo_display_comic_swf($post, $comic) {
	$file_url = ceo_pluginfo('baseurl') .'/' . ceo_clean_filename($comic);
	$height = get_post_meta( $post->ID, "fheight", true );
	$width = get_post_meta( $post->ID, "fwidth", true );
	if (empty($height)) $height = '300';
	if (empty($width)) $width = '100%';
	$output = "<object id=\"myId\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\"{$width}\" height=\"{$height}\">\r\n";
	$output .= "	<param name=\"movie\" value=\"".$file_url."\" />\r\n";
	$output .= "<!--[if !IE]>--><object type=\"application/x-shockwave-flash\" data=\"".$file_url."\" width=\"{$width}\" height=\"{$height}\"><!--<![endif]-->\r\n";
	$output .= "		<div>\r\n";
	$output .= "			<h1>Get Flash!</h1>\r\n";
	$output .= "				<p><a href=\"http://www.adobe.com/go/getflashplayer\"><img src=\"http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif\" alt=\"Get Adobe Flash player\" /></a></p>\r\n";
	$output .= "		</div>\r\n";
	$output .= "<!--[if !IE]>--></object><!--<![endif]--></object>\r\n";
	add_action('wp_footer', 'ceo_init_comic_swf');
	return apply_filters('ceo_display_comic_swf',$output);
}

/**
* Display text when image (comic) is hovered
* Text is taken from a custom field named "hovertext"
*/
function ceo_the_hovertext($override_post = null) {
	global $post;
	$post_to_use = !is_null($override_post) ? $override_post : $post;
	$hovertext = get_post_meta( $post_to_use->ID, "hovertext", true );
	return (empty($hovertext)) ? get_the_title($post_to_use->ID) : $hovertext;
}

function ceo_init_comic_swf() {
	wp_enqueue_script('swfobject', '', array(), false, true);
}

// This function will let authors who want to use comicpress as a way to output their books/text in a comic area as a page.
function ceo_display_comic_text($comic) {
	if (file_exists(ceo_pluginfo('basedir') . '/' .$comic)) {
		$output = nl2br(file_get_contents(ceo_pluginfo('basedir') . '/' .$comic));
	}
	return apply_filters('ceo_display_comic_text', $output);
}


// Do the thumbnail display functions here.
function ceo_display_comic_thumbnail($type = 'small', $override_post = null, $use_post_image = false, $setwidth = 0) {
	global $post;
	
	$post_to_use = !empty($override_post) ? $override_post : $post;

	// use_post_image if its set to true
	if ($use_post_image) {
		$post_image_id = get_post_thumbnail_id($post_to_use->ID);
		if ($post_image_id) {
			$thumbnail = wp_get_attachment_image_src( $post_image_id, 'post-thumbnail', false);
			if ($thumbnail) (string)$thumbnail = $thumbnail[0];
		}
	}
	
	if (empty($thumbnail)) {
		$thumb_found = get_comic_path($type, $post_to_use);
		
		$count = count($thumb_found);
		
		// adjust the thumbnail directories of all of them not just one, time to stop outputting them singularly and do array
		$thumbnail = array();
		if (!empty($thumb_found)) {
			foreach ($thumb_found as $thumb) {
				$thumbnail[] = ceo_pluginfo('baseurl') . '/' . ceo_clean_filename($thumb);
			}
		}
	}
	
	if (empty($thumbnail)) {
		// TODO: Need to determine the filename's extension at this point and mark it as such for those who don't have thumbnails.
		return false;
	}
	
	$output = '';
	if (is_array($thumbnail)) {
		foreach ($thumbnail as $thumb) {
			if ($setwidth) {
				$output .= '<img src="'.$thumb.'" alt="'.get_the_title($post_to_use).'" style="max-width:'.$setwidth.'px" class="comicthumbnail" title="'.get_the_title($post_to_use).'" />'."\r\n";
			} else {
				$output .= '<img src="'.$thumb.'" alt="'.get_the_title($post_to_use).'" width="" class="comicthumbnail" title="'.get_the_title($post_to_use).'" />'."\r\n";
			}
		}
	} else {
		if ($setwidth) {
			$output = '<img src="'.$thumbnail.'" alt="'.get_the_title($post_to_use).'" style="max-width:'.$setwidth.'px" class="comicthumbnail" title="'.get_the_title($post_to_use).'" />'."\r\n";
		} else {
			$output = '<img src="'.$thumbnail.'" alt="'.get_the_title($post_to_use).'" width="" class="comicthumbnail" title="'.get_the_title($post_to_use).'" />'."\r\n";
		}
	}
//	if ($count > 1) $output = $count.' comics attached.<br />'.$output;

	return apply_filters('ceo_display_comic_thumbnail', $output);
}

// TODO: Add the hovertext - rascal code and click to next INSIDE this.
function ceo_display_comic_image($post, $comic) {
	$file_url = ceo_pluginfo('baseurl') .'/'. ceo_clean_filename($comic);
	if (ceo_pluginfo('rascal_says')) {
		$alt_text = get_the_title($post);
	} else {
		$alt_text = ceo_the_hovertext($post);
	}
	$output = '<img src="'.$file_url.'" alt="'.$alt_text.'" title="'.$alt_text.'" />';
	return apply_filters('ceo_display_comic_image',$output);
}

// jquery code image swap by @brianarn
function ceo_display_comic() {
	global $post;
	$comics = get_comic_path('comic', $post);
	if (is_array($comics)) {
		$count = 1;
		$outputlist = '';
		foreach ($comics as $comic) {
			$comicsplit = explode(".", $comic); 
			switch (strtolower($comicsplit[1])) {
				case 'swf':
					$output .= '<div id="comic-'.$count.'" class="comicpane">';
					$output .= ceo_display_comic_swf($post, $comic);
					$output .= "</div>\r\n";
					$outputlist .= "<button id=\"show-".$count."\" type=\"button\">".$count."</button>\r\n";
					$count += 1;
					break;
				case 'txt':
				case 'inc':
				case 'htm':
					$output .= '<div id="comic-'.$count.'" class="comicpane">';
					$output .= ceo_display_comic_text($comic);
					$output .= "</div>\r\n";
					$outputlist .= "<button id=\"show-".$count."\" type=\"button\">".$count."</button>\r\n";
					$count += 1;
					break;
				case 'png':
				case 'gif':
				case 'jpg':
				case 'jpeg':
				case 'tif':
				case 'tiff':
				case 'bmp':
				default:
					$output .= '<div id="comic-'.$count.'" class="comicpane">';
					$output .= ceo_display_comic_image($post, $comic);
					$output .= "</div>\r\n";
					$outputlist .= "<button id=\"show-".$count."\" type=\"button\">".$count."</button>\r\n";
					$count += 1;
			}
		}
		if ($count > 2 && ceo_pluginfo('enable_multicomic_jquery')) {
			// Add the script stuff before the rest here.
			$output = $outputscript . $output;
		}
	}
	return $output;
}



if (!function_exists('ceo_comic_clicks_next')) {
	function ceo_comic_clicks_next($output) {
		global $post, $wp_query;
		if (is_search() || is_archive() || is_feed()) return $output;
		$hovertext = ceo_the_hovertext($post);
		$next_comic = ceo_get_next_comic_permalink();
		$class = '';
		if (ceo_pluginfo('rascal_says')) {
			$the_title = get_the_title($post);
			$class='class="tt"';
		} else {
			$the_title = ceo_the_hovertext($post);
		}
		$output = "<a {$class} href=\"{$next_comic}\" title=\"{$the_title}\">{$output}</a>\r\n";
		return $output;
	}
}

function ceo_rascal_says($output) {
	global $post, $wp_query;
	if (is_search() || is_archive() || is_feed()) return $output;
	$hovertext = get_post_meta( $post->ID, "hovertext", true );
	$href_to_use = "#";
	if (!empty($hovertext)) {
		$output = preg_replace('#title="([^*]*)"#', '', $output);
		$output = "<span class=\"tooltip\"><span class=\"top\">&nbsp;</span><span class=\"middle\">{$hovertext}</span><span class=\"bottom\">&nbsp;</span></span>{$output}\r\n";
	}
	if (ceo_pluginfo('comic_clicks_next')) {
		$href_to_use = ceo_get_next_comic_permalink();
		$output = "<a href=\"{$href_to_use}\" class=\"tt\" title=\"".$post->post_title."\">{$output}</a>";
	} else {
		$output = "<a class=\"tt\" href=\"{$href_to_use}\" title=\"".$post->post_title."\">{$output}</a>";
	}
	return apply_filters('ceo_rascal_says',$output);
}

if (ceo_pluginfo('rascal_says')) {
	add_filter('ceo_display_comic_image', 'ceo_rascal_says');
}

if (ceo_pluginfo('comic_clicks_next') && !ceo_pluginfo('rascal_says')) { 
	add_filter('ceo_display_comic_image', 'ceo_comic_clicks_next'); 
}

/**
* Find a comic file in the filesystem.
* @param string $folder The folder name to search.
* @param string $override_post A WP Post object to use in place of global $post.
* @param string $filter The $comic_filename_filters to use.
* @return string The relative path to the comic file, or false if not found.
*/

function get_comic_path($folder = 'comic', $override_post = null, $filter = 'default') {
	global $post, $comic_filename_filters, $comic_pathfinding_errors;

	$post_to_use = !empty($override_post) ? $override_post : $post;

	$meta_name = 'comic';

	if (function_exists('xlanguage_current_language_code')) 
		$meta_name .= '-'.xlanguage_current_language_code();

	$comicfile = get_post_meta( $post_to_use->ID, $meta_name, false );

// Backswards compatibility here
	
	switch ($folder) {
		case "medium": $subfolder_to_use = ceo_pluginfo('medium_thumbnail_folder'); break;
		case "small": $subfolder_to_use = ceo_pluginfo('small_thumbnail_folder'); break;
		case "comic": default: $subfolder_to_use = ceo_pluginfo('comic_folder'); break;
	}
	
	$folder_to_use = ceo_pluginfo('basedir') . '/' . $subfolder_to_use;

//	if (!file_exists($folder_to_use . '/' . $comicfile) && $folder !== 'comic') 
//		$subfolder_to_use = ceo_pluginfo('comic_folder'); 
	
	if (!empty($comicfile)) {
		// return this as an array if we want to include in the future multiple comics found type thing, keeping it compatible.
		$newresults = array();
		foreach ($comicfile as $comic) {
			if (!file_exists($folder_to_use . '/' . $comic) && $folder !== 'comic') 
				$subfolder_to_use = ceo_pluginfo('comic_folder'); 
			$newresults[] = $subfolder_to_use .'/' .$comic;
		}
		return $newresults;
		
	} else {
		// backwards compatibility
		if (isset($comic_filename_filters[$filter])) {
			$filter_to_use = $comic_filename_filters[$filter];
		} else {
			$filter_to_use = '{date}*.*';
		}
	
		$post_date = mysql2date(CP_DATE_FORMAT, $post_to_use->post_date);
		$filter_with_date = str_replace('{date}', $post_date, $filter_to_use);
		
		$results = array();
		
		if (count($results = glob("${folder_to_use}/${filter_with_date}")) > 0) {
	
			$newresults = array();
			foreach ($results as $result) {
				// Strip the base directory off.
				$newresults[] = str_replace(ceo_pluginfo('basedir'), '', $result);
			}
			return $newresults;
		} else {
			// fallback to the comics directory
			$folder_to_use = ceo_pluginfo('basedir') . '/' . ceo_pluginfo('comic_folder');
			if (count($results = glob("${folder_to_use}/${filter_with_date}")) > 0) {
				
				$newresults = array();
				foreach ($results as $result) {
					// Strip the base directory off.
					$newresults[] = str_replace(ceo_pluginfo('basedir'), '', $result);
				}
				return $newresults;
			}
		}	
	}
	
	$comic_pathfinding_errors[] = sprintf(__("Unable to find the file in the <strong>%s</strong> folder that matched the pattern <strong>%s</strong>. Check your WordPress and ComicPress settings.", 'comicpress'), $folder_to_use, $filter_with_date);
	return false;
}


/**
* Find a comic file in the filesystem and return an absolute URL to that file.
* @param string $folder The folder name to search.
* @param string $override_post A WP Post object to use in place of global $post.
* @param string $filter The $comic_filename_filters to use.
* @return string The absolute URL to the comic file, or false if not found.
*/ 
function get_comic_url($folder = 'comic', $override_post = null, $filter = 'default') {
	if (($results = get_comic_path($folder, $override_post, $filter)) !== false) {
		$newresults = array();
		foreach ($results as $result) {
			$newresults[] = ceo_pluginfo('baseurl') .'/'. $result;
		}
		return $newresults;
	}
	return false;
}


?>