<?php

add_action('admin_init', 'ceo_admin_init');

function ceo_admin_init() {
	add_filter('manage_edit-comic_columns', 'ceo_add_new_comic_columns');
	add_action('manage_posts_custom_column', 'ceo_manage_comic_columns', 10, 2);
	
	add_action('add_meta_boxes', 'ceo_add_comic_in_post');
	add_action('save_post', 'ceo_handle_edit_save_comic', 10, 2 );
	
	add_filter("manage_edit-chapters_columns", 'ceo_chapters_columns');
	add_filter('manage_edit-chapters_sortable_columns', 'ceo_chapters_sortable_columns' );
	add_filter('manage_chapters_custom_column', 'ceo_chapters_add_column_value', 10, 3);
	add_action('chapters_add_form_fields', 'ceo_chapters_menu_order_add_form_field');
	add_action('chapters_edit_form_fields', 'ceo_chapters_menu_order_edit_form_field');
	
	add_action('edited_chapters', 'ceo_chapters_save_value', 10, 2);
	add_action('quick_edit_custom_box', 'ceo_chapters_quick_edit_menu_order', 10, 3);
	
	add_action('create_term', 'ceo_chapters_add_edit_menu_order');
//	add_filter('get_terms_args', 'ceo_chapters_find_menu_orderby');	
}

function ceo_chapters_add_edit_menu_order($term_id) {
		global $wpdb;
		if (isset($_POST['ceo_chapter_order'])) {
			$wpdb->update($wpdb->terms, array('menu_order' => (int)$_POST['ceo_chapter_order']), array('term_id' => $term_id));	
	}		
}

/*
function ceo_chapters_find_menu_orderby($args) {
	if ('menu_order' === $args['orderby']) {
		add_filter('get_terms_orderby', 'ceo_chapters_edit_menu_orderby');
	}
	return $args;
}


function ceo_chapters_edit_menu_orderby() {
	//This is a one-off, so that we don't disrupt queries that may not use menu_order.
	remove_filter('get_terms_orderby', 'ceo_chapters_edit_menu_orderby');
	return "menu_order";	
}
*/

function ceo_chapters_quick_edit_menu_order ($column_name, $screen, $name = '') {
	if ( did_action( 'quick_edit_custom_box' ) !== 1 ) return;
	if (($column_name != 'menu_order') && ($name != 'chapters') && ($screen != 'edit-tags') && empty($name)) return;
	$menu_order_field = '<fieldset><div class="inline-edit-col"><label><span class="title">' . __( 'Order' , 'term-menu-order') . '</span><span class="input-text-wrap"><input class="ptitle" name="ceo_chapter_order" type="text" value="" /></span></label></div></fieldset>';
	$menu_order_field .= '<script type="text/javascript">
	</script>';
	echo $menu_order_field;
}

function ceo_chapters_save_value($term_id, $tt_id) {
	if (!$term_id) return;
	global $wpdb;
	if (isset($_POST['ceo_chapter_order']))
		$itwork = $wpdb->update($wpdb->terms, array('menu_order' => (int)$_POST['ceo_chapter_order']), array('term_id' => $term_id));
}

function ceo_chapters_columns($chapter_columns) {
	wp_register_style('comiceasel-chapters-style', ceo_pluginfo('plugin_url').'/css/chapters.css');
	wp_enqueue_style('comiceasel-chapters-style');
	$new_columns['cb'] = '<input type="checkbox" />';
	$new_columns['id'] = __('ID', 'comiceasel');
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
	switch ($custom_column) {
		case 'id':
			echo $term_id;
			break;
		case 'menu_order':
			$taxonomy = (isset($_POST['taxonomy'])) ? $_POST['taxonomy'] : $_GET['taxonomy'];
			$term = get_term($term_id, $taxonomy);
			echo $term->$custom_column;
			break;
	}
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
 
	$new_columns['chapter'] = __('Chapter', 'comiceasel');
	$new_columns['characters'] = __('Characters', 'comiceasel');
	$new_columns['locations'] = __('Location', 'comiceasel');
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
					$term_list_chapters[] = '<a href="'.admin_url('edit.php?post_type=comic&chapters='.$term->name).'">'.$term->name.'</a>';
				}
				echo join(', ', $term_list_chapters );
			}
	        break;
	case 'characters':
			$allterms = get_the_terms( $id, 'characters');
			if (!empty($allterms) && !isset($allterms->errors)) {
				foreach ($allterms as $term) {
					$term_list_characters[] = '<a href="'.admin_url('edit.php?post_type=comic&characters='.$term->name).'">'.$term->name.'</a>';
				}
				echo join(', ', $term_list_characters );
			}
	        break;
	case 'locations':
			$allterms = get_the_terms( $id, 'locations');
			if (!empty($allterms) && !isset($allterms->errors)) {
				foreach ($allterms as $term) {
					$term_list_locations[] = '<a href="'.admin_url('/wp-admin/edit.php?post_type=comic&locations='.$term->name).'">'.$term->name.'</a>';
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

function ceo_edit_toggles_in_post($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
?>
<table class="widefat">
	<tr>
		<th scope="row"><label for="comic-gallery"><?php _e('Multi-Comic Support?','comiceasel'); ?></label></th>
		<td>
			<input id="comic-gallery" name="comic-gallery" type="checkbox" value="1" <?php checked(1, get_post_meta( $post->ID, 'comic-gallery', true )); ?> />
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="comic-gallery-full"><?php _e('Display the comics as full sized?','comiceasel'); ?></label>
			<span style="font-size: 9px;"><?php _e('Not checking this will use the gallery thumbnail view, if multi-comic is enabled.','comiceasel'); ?></span>
		</th>
		<td>
			<input id="comic-gallery-full" name="comic-gallery-full" type="checkbox" value="1" <?php checked(1, get_post_meta( $post->ID, 'comic-gallery-full', true )); ?> />
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="comic-gallery-jquery"><?php _e('Use the jQuery page flipper?','comiceasel'); ?></label></th>
		<td>
			<input id="comic-gallery-jquery" name="comic-gallery-jquery" type="checkbox" value="1" <?php checked(1, get_post_meta( $post->ID, 'comic-gallery-jquery', true )); ?> />
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="comic-gallery-columns"><?php _e('If not full sized, how many gallery rows to use?','comiceasel'); ?></label></th>
		<td style="width: 30%">
			<?php
				$column_count = esc_attr( get_post_meta( $post->ID, 'comic-gallery-columns', true ));
				if (empty($column_count) || ($column_count > 10) || ($column_count < 1)) $column_count = 5;
			?>
			<input id="comic-gallery-columns" name="comic-gallery-columns" style="width: 40px;" type="text" value="<?php echo $column_count; ?>"  />
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="comic-open-lightbox"><?php _e('Open comic up with Lightbox?','comiceasel'); ?></label></th>
		<td>
			<input id="comic-open-lightbox" name="comic-open-lightbox" type="checkbox" value="1" <?php checked(1, get_post_meta( $post->ID, 'comic-open-lightbox', true )); ?>"  />
		</td>
	</tr>
</table>
<?php
}

function ceo_edit_hovertext_in_post($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
	$hovertext = esc_attr( get_post_meta( $post->ID, 'comic-hovertext', true ) );
	if (empty($hovertext)) $hovertext = esc_attr( get_post_meta($post->ID, 'hovertext', true));
?>
	The text placed here will appear when you mouse over the comic.<br />
	<textarea name="comic-hovertext" id="comic-hovertext" class="admin-comic-hovertext" style="width:100%"><?php echo $hovertext; ?></textarea>
<?php
}

function ceo_edit_transcript_in_post($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
	$transcript = esc_attr( get_post_meta($post->ID, 'transcript', true));
?>
	The text placed here will appear if you use the [transcript] shortcode.<br />
	<textarea name="transcript" id="transcript" class="admin-transcript" style="width:100%; height: 100px;"><?php echo $transcript; ?></textarea>
<?php
}

function ceo_edit_html_above_comic($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
?>
	The html placed here will appear above the comic.<br />
	<textarea name="comic-html-above" id="comic-html-above" class="admin-comic-html-above" style="width:100%"><?php echo get_post_meta( $post->ID, 'comic-html-above', true ); ?></textarea>
<?php
}

function ceo_edit_html_below_comic($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
?>
	The html placed here will appear below the comic.<br />
	<textarea name="comic-html-below" id="comic-html-below" class="admin-comic-html-below" style="width:100%"><?php echo get_post_meta( $post->ID, 'comic-html-below', true ); ?></textarea>
<?php
}

function ceo_add_comic_in_post() {
	add_meta_box('ceo_comic_in_post', __('Comic', 'comiceasel'), 'ceo_edit_comic_in_post', 'comic', 'side', 'high');
	if (!defined('CEO_FEATURE_DISABLE_MISC'))
		add_meta_box('ceo_toggle_in_post', __('Misc. Comic Functionality', 'comiceasel'), 'ceo_edit_toggles_in_post', 'comic', 'side', 'low');
	if (!defined('CEO_FEATURE_DISABLE_HOVERTEXT'))
		add_meta_box('ceo_hovertext_in_post', __('Alt (Hover) Text', 'comiceasel'), 'ceo_edit_hovertext_in_post', 'comic', 'normal', 'high');
	if (!defined('CEO_FEATURE_DISABLE_TRANSCRIPT'))
		add_meta_box('ceo_transcript_in_post', __('Transcript', 'comiceasel'), 'ceo_edit_transcript_in_post', 'comic', 'normal', 'high');
	if (!defined('CEO_FEATURE_DISABLE_HTML')) {
		add_meta_box('ceo_html_above_comic', __('HTML (Above) Comic', 'comiceasel'), 'ceo_edit_html_above_comic', 'comic', 'normal', 'high');
		add_meta_box('ceo_html_below_comic', __('HTML (Below) Comic', 'comiceasel'), 'ceo_edit_html_below_comic', 'comic', 'normal', 'high');
	}
}

function ceo_handle_edit_save_comic($post_id, $post) {
	global $post;

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post->ID;
	}

	/* Verify the nonce before proceeding. */
	if ( !isset( $_POST['comic_nonce'] ) || !wp_verify_nonce( $_POST['comic_nonce'], basename( __FILE__ ) ) )
		return $post_id;
	
	/* Get the post type object. */
	$post_type = get_post_type_object( $post->post_type );
	
	/* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	$meta_array = array(
			'transcript',
			'comic-html-above',
			'comic-html-below',
			'comic-hovertext',
			'comic-gallery',
			'comic-gallery-columns',
			'comic-open-lightbox',
			'comic-gallery-full',
			'comic-gallery-jquery'
			);
			
	foreach ($meta_array as $meta_key) {
		$new_meta_value = ( isset( $_POST[$meta_key] ) ? esc_textarea( $_POST[$meta_key] ) : '' );
		$meta_value = get_post_meta( $post_id, $meta_key, true );
		if ( $new_meta_value && '' == $meta_value )
			add_post_meta( $post_id, $meta_key, $new_meta_value, true );
		elseif ( $new_meta_value && $new_meta_value != $meta_value )
			update_post_meta( $post_id, $meta_key, $new_meta_value );
		elseif ( '' == $new_meta_value && $meta_value )
			delete_post_meta( $post_id, $meta_key, $meta_value );
	}
}
