<?php

add_action('admin_init', 'ceo_admin_init');

function ceo_admin_init() {
	add_filter('manage_edit-comic_columns', 'ceo_add_new_comic_columns');
	add_action('manage_posts_custom_column', 'ceo_manage_comic_columns', 10, 2);
	add_action('add_meta_boxes', 'ceo_add_comic_in_post');
	add_action( 'save_post', 'ceo_handle_edit_save_comic', 10, 2 );
	
	add_filter("manage_edit-chapters_columns", 'ceo_chapters_columns');
	add_filter('manage_edit-chapters_sortable_columns', 'ceo_chapters_sortable_columns' );
	add_filter('manage_chapters_custom_column', 'ceo_chapters_add_column_value', 10, 3);
	add_action('chapters_add_form_fields', 'ceo_chapters_menu_order_add_form_field');
	add_action('chapters_edit_form_fields', 'ceo_chapters_menu_order_edit_form_field');
	add_action('edited_chapters', 'ceo_chapters_save_value', 10, 2);
	add_action('quick_edit_custom_box', 'ceo_chapters_quick_edit_menu_order', 10, 3);
}

function ceo_chapters_quick_edit_menu_order($column_name, $screen, $name = '') {
	if (($column_name != 'menu_order') && ($name != 'chapters') && ($screen != 'edit-tags')) return;
	echo '<fieldset><div class="inline-edit-col"><label><span class="title">' . __( 'Order' , 'comiceasel') . '</span><span class="input-text-wrap"><input class="ptitle" name="ceo_chapter_order" type="text" value="" /></span></label></div></fieldset>';
}

function ceo_chapters_save_value($term_id, $tt_id) {
	if (!$term_id) return;
	global $wpdb;
	if (isset($_POST['ceo_chapter_order']))
		$itwork = $wpdb->update($wpdb->terms, array('menu_order' => (int)$_POST['ceo_chapter_order']), array('term_id' => $term_id));
}

function ceo_chapters_columns($chapter_columns) {
	$new_columns['cb'] = '<input type="checkbox" />';
	$new_columns['name'] = __('Name', 'comiceasel');
	$new_columns['slug'] = __('Slug', 'comiceasel');
	$new_columns['description'] = __('Description', 'comiceasel');
	$new_columns['posts'] = __('Comics', 'comiceasel');
	$new_columns['menu_order'] = __('Order', 'comiceasel');
	return $new_columns;
}

function ceo_chapters_sortable_columns( $columns ) {
	$columns['menu_order'] = 'menu_order';
	return $columns;
}

function ceo_chapters_add_column_value($empty = '', $custom_column, $term_id) {
	$taxonomy = (isset($_POST['taxonomy'])) ? $_POST['taxonomy'] : $_GET['taxonomy'];
	$term = get_term($term_id, $taxonomy);
	return $term->$custom_column;
}

function ceo_chapters_menu_order_add_form_field() {		
	$form_field = '<div class="form-field"><label for="ceo_chapter_order">' . __('Chapter Order', 'comiceasel') . '</label><input name="ceo_chapter_order" id="ceo_chapter_order" type="text" value="0" size="10" /><p>' . __('This defines what order the chapter is in. 0 = do not order.', 'comiceasel') . '</p></div>';
	echo $form_field;
}

function ceo_chapters_menu_order_edit_form_field($term) {
	$form_field = '<tr class="form-field"><th scope="row" valign="top"><label for="ceo_chapter_order">' . __('Chapter Order', 'comiceasel')  . '</label></th><td><input name="ceo_chapter_order" id="ceo_chapter_order" type="text" value="'.$term->menu_order.'" size="10" /><p class="description">' . __('This defines what order the chapter is in. 0 = do not order.', 'comiceasel') .'</p></td></tr>';
	echo $form_field;
}

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
<?php 
	if ( has_post_thumbnail()) {
		echo ceo_display_comic_thumbnail('medium', $post); 
} else { 
		_e('To add a comic image, use the featured image link to add a featured image.  After it uploads, click the "use as featured image".','comiceasel'); ?><br />
<?php } ?>
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

function ceo_add_comic_in_post() {
	add_meta_box('ceo_comic_in_post', __('Comic', 'comiceasel'), 'ceo_edit_comic_in_post', 'comic', 'side', 'high');
	add_meta_box('ceo_hovertext_in_post', __('Alt (Hover) Text', 'comiceasel'), 'ceo_edit_hovertext_in_post', 'comic', 'normal', 'high');
}

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


function ceo_chapters_activate() {
	global $wpdb;
	$init_query = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'menu_order'");
	if (!$init_query) {
		$sql = "ALTER TABLE `{$wpdb->terms}` ADD `menu_order` INT (11) NOT NULL DEFAULT 0;";
		$wpdb->query($sql);
	}
}

function ceo_chapters_deactivate() {
	global $wpdb;
	$sql = "ALTER TABLE `{$wpdb->terms}` DROP COLUMN `menu_order`;";
	$result = $wpdb->query($sql);	
}
