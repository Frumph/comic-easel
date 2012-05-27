<?php

add_filter('manage_edit-comic_columns', 'ceo_add_new_comic_columns');

function ceo_add_new_comic_columns($comic_columns) {
	$new_columns['cb'] = '<input type="checkbox" />';

	$new_columns['title'] = __('Comic Title', 'comiceasel');
 
	$new_columns['chapter'] = __('Chapter','comiceasel');
	$new_columns['characters'] = __('Characters','comiceasel');
	$new_columns['locations'] = __('Location','comiceasel');
	$new_columns['tags'] = __('Tags', 'comiceasel');
	$new_columns['date'] = _x('Date', 'column name');
	$new_columns['comicimages'] = __('Thumbnail', 'comiceasel');
 
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
			$comicthumb = ceo_display_comic_thumbnail('thumbnail', $post); 
			if (!$comicthumb) { echo 'No Comic Found.'; } else {
				echo $comicthumb;
			}
		break;
	default:
		break;
	} // end switch
}

function ceo_edit_comic_in_post($post) {  ?>
<div class="admin-comicbox" style="margin:0; padding:0; overflow:hidden;">
	<center>
	<?php echo ceo_display_comic_thumbnail('medium', $post); ?><br />
	</center>
</div>
<?php
}

function ceo_edit_hovertext_in_post($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_hovertext_nonce' );
?>
	The text placed here will appear when you mouse over the comic.<br />
	<textarea name="comic-hovertext" id="comic-hovertext" class="admin-comic-hovertext" style="width:100%"><?php echo esc_attr( get_post_meta( $post->ID, 'hovertext', true ) ); ?></textarea>
<?php
}

add_action('add_meta_boxes', 'ceo_add_comic_in_post');

function ceo_add_comic_in_post() {
	add_meta_box('ceo_comic_in_post', __('Comic', 'comiceasel'), 'ceo_edit_comic_in_post', 'comic', 'side', 'high');
	add_meta_box('ceo_hovertext_in_post', __('Alt (Hover) Text', 'comiceasel'), 'ceo_edit_hovertext_in_post', 'comic', 'normal', 'high');
}

add_action( 'save_post', 'ceo_handle_edit_save_comic', 10, 2 );

function ceo_handle_edit_save_comic($post_id, $post) {
	/* Verify the nonce before proceeding. */
	if ( !isset( $_POST['comic_hovertext_nonce'] ) || !wp_verify_nonce( $_POST['comic_hovertext_nonce'], basename( __FILE__ ) ) )
		return $post_id;
	
	/* Get the post type object. */
	$post_type = get_post_type_object( $post->post_type );
	
	/* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;
	
	/* Get the posted data and sanitize it for use as an HTML class. */
	$new_meta_value = ( isset( $_POST['comic-hovertext'] ) ? esc_textarea( $_POST['comic-hovertext'] ) : '' );
	
	/* Get the meta key. */
	$meta_key = 'hovertext';
	
	/* Get the meta value of the custom field key. */
	$meta_value = get_post_meta( $post_id, $meta_key, true );
	
	/* If a new meta value was added and there was no previous value, add it. */
	if ( $new_meta_value && '' == $meta_value )
		add_post_meta( $post_id, $meta_key, $new_meta_value, true );
	
	/* If the new meta value does not match the old value, update it. */
	elseif ( $new_meta_value && $new_meta_value != $meta_value )
		update_post_meta( $post_id, $meta_key, $new_meta_value );
	
	/* If there is no new meta value but an old value exists, delete it. */
	elseif ( '' == $new_meta_value && $meta_value )
		delete_post_meta( $post_id, $meta_key, $meta_value );
}


?>