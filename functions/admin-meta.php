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
<div class="inside" style="overflow: hidden; width: 100%;">
	<div id="comicthumbs">
		<center>
		<?php echo ceo_display_comic_thumbnail('medium', $post, false, 200); ?><br />
		</center>
	</div>
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
	add_meta_box('ceo_comic_in_post', __('Comic', 'comiceasel'), 'ceo_edit_comic_in_post', 'comic', 'side', 'high');
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