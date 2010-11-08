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
		<td valign="top">
			<div id="file-uploader-demo1">		
				<noscript>			
					<p>Please enable JavaScript to use file uploader.</p>
					<!-- or put a simple form for upload here -->
				</noscript>         

			</div>
<script>        
	function createUploader(){            
		var uploader = new qq.FileUploader({
			element: document.getElementById('file-uploader-demo1'),
			action: '<?php echo admin_url( 'admin-ajax.php'  ); ?>',
			params: {
				action: 'ceo_uploader'
			}
		});           
	}
	// in your app create uploader as soon as the DOM is readyi
	// don't wait for the window to load  
	window.onload = createUploader;     
</script> 
<hr>
			Meta Box Inside a Post
			TODO<br />
			<ol>
			<li>When file is uploaded via this script create the 2 thumbnails necessary using the ceo_pluginfo paths that are set</li>
			<li>Update the 'comic' custom metafield with the filename (not full location, just filename)</li>
			<li>Check if comic custom field exists, if so, display comic thumbnail that appears</li>
			<li>If custom field does not exist, check if comic exists via date</li>
			<li>If not by date or custom field then display no comic exists</li>
			</ol>
		</td>
		<td valign="top" style="width: 240px;">
		<center>
		<?php echo ceo_display_comic_thumbnail('small', $post, false, 198); ?>
		</center>
		<br />
		Display Current Comic Thumbnail(s) that is set in this area.  Basically do a loop through the meta fields for 'comic' and display each thumbnail here of all that are attached.
		</td>
	</tr>
	<tr>
		<td colspan="3" style="border-top: solid 1px #000;">
		Display 'selection' box where you can reselect a different comic that is already available here instead of uploading.<br />
		This also needs to be ajaxified where when clicking the select button it will update the custom post meta field with the appropriate filename for the comic.<br />
		If multiple comics selected it will create a new comic field for all of them.
		</td>
	</tr>
	</table>
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

?>
