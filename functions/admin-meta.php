<?php

add_filter('manage_edit-comic_columns', 'ceo_add_new_comic_columns');

function ceo_add_new_comic_columns($comic_columns) {
	$new_columns['cb'] = '<input type="checkbox" />';

	$new_columns['title'] = _x('Comic Title Name', 'column name');
 
	$new_columns['chapter'] = __('Chapter','easel');
	$new_columns['characters'] = __('Characters','easel');
	$new_columns['locations'] = __('Location','easel');
	$new_columns['tags'] = __('Tags', 'easel');
 
	$new_columns['date'] = _x('Date', 'column name');
	$new_columns['comicimages'] = _x('Comic', 'column name');
 
	return $new_columns;
}

add_action('manage_posts_custom_column', 'ceo_manage_comic_columns', 10, 2);
 
function ceo_manage_comic_columns($column_name, $id) {
	global $wpdb;
	switch ($column_name) {
	case 'chapter':
			$allterms = get_the_terms( $id, 'chapters');
			if (!empty($allterms) && !isset($allterms->errors)) {
				foreach ($allterms as $term) {
					$term_list_chapters[] = '<a href="'.home_url(ceo_clean_filename('/wp-admin/edit.php?post_type=comic&chapters='.$term->name)).'">'.$term->name.'</a>';
				}
				echo join(', ', $term_list_chapters );
			}
	        break;
	case 'characters':
			$allterms = get_the_terms( $id, 'characters');
			if (!empty($allterms) && !isset($allterms->errors)) {
				foreach ($allterms as $term) {
					$term_list_characters[] = '<a href="'.home_url(ceo_clean_filename('/wp-admin/edit.php?post_type=comic&characters='.$term->name)).'">'.$term->name.'</a>';
				}
				echo join(', ', $term_list_characters );
			}
	        break;
	case 'locations':
			$allterms = get_the_terms( $id, 'locations');
			if (!empty($allterms) && !isset($allterms->errors)) {
				foreach ($allterms as $term) {
					$term_list_locations[] = '<a href="'.home_url(ceo_clean_filename('/wp-admin/edit.php?post_type=comic&locations='.$term->name)).'">'.$term->name.'</a>';
				}
				echo join(', ', $term_list_locations );
			}
	        break;
	case 'comicimages':
			$post = &get_post($id);
			$comicthumb = ceo_display_comic_thumbnail('small', $post, false, 100); 
			if (!$comicthumb) { echo 'No Comic Found.'; } else {
				echo $comicthumb;
			}
		break;
	default:
		break;
	} // end switch
}

function ceo_edit_comic_in_post($post) { 
?>
<div class="inside" style="overflow: hidden">
	<table>
		<td valign="top" style="width:300px;">
		<! -- comic uploader button -->
			<div id="file-uploader-demo1">		
				<noscript>			
					<p>Please enable JavaScript to use file uploader.</p>
					<!-- or put a simple form for upload here -->
				</noscript>         
			</div>
		<!-- end comic uploader button -->
<hr>
		<br />
		<!-- selectbox for file attaching from DIR -->
<?php
			// open the current directory
$comic_path = ceo_pluginfo('comic_path');
			// define an array to hold the files
$dirhandle = opendir($comic_path);
if ($dirhandle) {
	// loop through all of the files
	while (false !== ($fname = readdir($dirhandle))) {
		// if the file is not this file, and does not start with a '.' or '..',
		// then store it for later display
		if (($fname != '.') && ($fname != '..') && ($fname != basename($_SERVER['PHP_SELF']))) {
			// store the filename
			$files[] = (is_dir( "./$fname" )) ? "(Dir) {$fname}" : $fname;
		}
	}
	// close the directory
	closedir($dirhandle);
}
			?>
			<div id="comicfileattacharea">
			<select id="comicfile" name="comicfile" size="6" multiple>
			<?php
			if (empty($files)) {
				echo '<option value="There are no files available">No Files';
			} else {
			// Now loop through the files, echoing out a new select option for each one
				foreach( $files as $fname ) {
					echo "<option value=\"{$fname}\">{$fname}\r\n";
				}
			}
			?>
			</select>
			</div>
			<br />
			<div class="comicfileattach">
			<?php if (!empty($files)) { ?>
				<INPUT type="button" value="Attach" name="button2" onClick="comicfileadd(<?php echo $post->ID; ?>)"> 
			<?php } ?>
			</div>
			<br />
		<!-- end selectbox attacher -->
		</td>
		<td valign="top" style="width: 240px;">
		<center>
		  <!-- DIV added to enable auto update function -->
			<div id="comicthumbsremove">
				<?php echo ceo_display_comic_thumbnail_editor('small', $post, false, 198); ?><br />
			</div>
		</center>
		</td>
	</tr>
	</table>
	<script>        
	function createUploader(){            
		var uploader = new qq.FileUploader({
			element: document.getElementById('file-uploader-demo1'),
			action: '<?php echo admin_url( 'admin-ajax.php'  ); ?>',
			params: {
				action: 'ceo_uploader',
				post_id: '<?php echo $post->ID ?>'
			},
			onComplete: function(id, fileName, responseJSON){
				//refreash thumbnail DIV
				 getdata(ajaxurl + '?action=ceo_thumb_update&post_id=<?php echo $post->ID ?>','comicthumbsremove');
			}
		});           
	}
	// in your app create uploader as soon as the DOM is readyi
	// don't wait for the window to load  
	window.onload = createUploader;   
</script> 
</div>
<?php
}

function ceo_edit_hovertext_in_post($post) { 
?>
<div class="inside" style="overflow: hidden">
	<table>
		<td valign="top">
			Alt Text (Hover) - This is text that is displayed when the mouse is over the comic.<br />
		</td>
	</tr>
	</table>
</div>
<?php
}

add_action('add_meta_boxes', 'ceo_add_comic_in_post');

function ceo_add_comic_in_post() {
	add_meta_box('ceo_comic_in_post', __('Comic', 'comiceasel'), 'ceo_edit_comic_in_post', 'comic', 'normal', 'high');
	add_meta_box('ceo_hovertext_in_post', __('Alt (Hover) Text', 'comiceasel'), 'ceo_edit_hovertext_in_post', 'comic', 'normal', 'high');
}

function ceo_handle_edit_save_comic($post_id) {
/*	$moods_directory = ceo_pluginfo('moods_directory');
	if (!empty($moods_directory) && $moods_directory != 'none') {
		$currentmood = get_post_meta( $post_id, "mood", true );
		if (isset($_POST['postmood']) && $_POST['postmood'] !== $currentmood) {
			$postmood = $_POST['postmood'];
			update_post_meta($post_id, 'mood', $postmood);
		}
	} */
}

// Do the thumbnail display functions here.
function ceo_display_comic_thumbnail_editor($type = 'small', $override_post = null, $use_post_image = false, $setwidth = 0) {
	global $post;
	$thumbnail = '';
	$post_to_use = !empty($override_post) ? $override_post : $post;
	$thumburl = ceo_pluginfo('thumbnail_small_url');
	$thumbnail = get_post_meta($post_to_use->ID, 'comic');
	foreach ($thumbnail as $thumb) {
		if ($setwidth) {
			echo '<div id='.$thumb.'><img src="'.$thumburl.'/'.$thumb.'" alt="'.get_the_title($post_to_use).'" style="max-width:'.$setwidth.'px" class="comicthumbnail" title="'.get_the_title($post_to_use->ID).'" /><INPUT type="button" value="Remove" name="'.$thumb.'" onClick="comicfileremove('.$post->ID.',\''.$thumb.'\')"></div>'."\r\n";
		} else {
			echo '<img src="'.$thumburl.'/'.$thumb.'" alt="'.get_the_title($post_to_use).'" class="comicthumbnail" title="'.get_the_title($post_to_use).'" /><INPUT type="button" value="Remove" name="button2" onClick="comicfileremove('.$post->ID.','.$thumb.')">'."\r\n";
		}
	}
}

?>
