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


	add_action( 'restrict_manage_posts', 'ceo_filter_restrict_manage_posts' );
	add_filter( 'parse_query', 'ceo_taxonomy_filter_post_type_request' );	
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
	$menu_order_field = '<fieldset><div class="inline-edit-col"><label><span class="title">' . __( 'Order' , 'comiceasel') . '</span><span class="input-text-wrap"><input class="ptitle" name="ceo_chapter_order" type="text" value="" /></span></label></div></fieldset>';
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
	wp_register_style('comiceasel-chapters-style', ceo_pluginfo('plugin_url').'css/chapters.css');
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
	$form_field = '<div class="form-field"><label for="ceo_chapter_order">'.ucwords(ceo_pluginfo('chapter_type_slug_name')).' '.__('Order', 'comiceasel') . '</label><input name="ceo_chapter_order" id="ceo_chapter_order" type="text" value="0" size="10" /><p>' . __('This defines what order the taxonomy is in. 0 = do not order.', 'comiceasel') . '</p></div>';
	echo $form_field;
}

function ceo_chapters_menu_order_edit_form_field($term) {
	$form_field = '<tr class="form-field"><th scope="row" valign="top"><label for="ceo_chapter_order">' .ucwords(ceo_pluginfo('chapter_type_slug_name')).' '.__('Order', 'comiceasel')  . '</label></th><td><input name="ceo_chapter_order" id="ceo_chapter_order" type="text" value="'.$term->menu_order.'" size="10" /><p class="description">' . __('This defines what order the taxonomy is in. 0 = do not order.', 'comiceasel') .'</p></td></tr>';
	echo $form_field;
}

function ceo_add_new_comic_columns($comic_columns) {
	$new_columns['cb'] = '<input type="checkbox" />';
	$new_columns['title'] = __('Comic Title', 'comiceasel');
	$new_columns['chapter'] = ucwords(ceo_pluginfo('chapter_type_slug_name'));
	$new_columns['characters'] = __('Characters', 'comiceasel');
	if (ceo_pluginfo('allow_comics_to_have_categories'))
		$new_columns['categories'] = __('Category', 'comiceasel');
	$new_columns['locations'] = __('Location', 'comiceasel');
	$new_columns['tags'] = __('Tags', 'comiceasel');
	$new_columns['date'] = __('Date', 'comiceasel');
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
			$post = get_post($id);
			$comicthumb = ceo_display_comic_thumbnail('thumbnail', $post, array(120,0)); 
			if (!$comicthumb) { echo __('No thumbnail Found.','comiceasel'); } else {
				echo $comicthumb;
			}
		break;
	default:
		break;
	} // end switch
}

// Filter the request to just give posts for the given taxonomy, if applicable.
function ceo_filter_restrict_manage_posts() {
    global $typenow;
	if ('comic' == $typenow) {
		$post_types = get_post_types( array( '_builtin' => false ) );
		if ( in_array( $typenow, $post_types ) ) {
    		$filters = get_object_taxonomies( $typenow );
			// remove post tag
			$filters = array_diff($filters, array('post_tag'));
			foreach ( $filters as $tax_slug ) {
				$tax_obj = get_taxonomy( $tax_slug );
				wp_dropdown_categories( array(
					'show_option_all' => __('Show All '.$tax_obj->label ),
					'taxonomy' 	  => $tax_slug,
					'name' 		  => $tax_obj->name,
					'orderby' 	  => 'name',
					'selected' 	  => (isset($_GET[$tax_slug])) ? $_GET[$tax_slug] : '',
					'hierarchical' 	  => $tax_obj->hierarchical,
					'show_count' 	  => false,
					'hide_empty' 	  => true
				) );
			}
		}
	}
}

function ceo_taxonomy_filter_post_type_request( $query ) {
	global $pagenow, $typenow;
	if ( 'edit.php' == $pagenow ) {
		$filters = get_object_taxonomies( $typenow );
		foreach ( $filters as $tax_slug ) {
			$var = &$query->query_vars[$tax_slug];
			if ( isset( $var ) ) {
				$term = get_term_by( 'id', $var, $tax_slug );
				if (!empty($term)) $var = $term->slug;
			}
		}
	}
}

function ceo_edit_comic_in_post($post) {  ?>
<div class="admin-comicbox" style="margin:0; padding:0; overflow:hidden;">
<?php 
		$output = '<ul><ol>';
		$output .= '<li>'.__('Add a title to the comic.&nbsp; Titles must be alpha-numerical, not just numbers.','comiceasel').'</li>';
		$output .= '<li>'.__('Add some info to the blog section of the comic if you want to (not required).','comiceasel').'</li>';
		$output .= '<li>'.__('Set the date/time you want it to be published, leave it as Publish Immediately if you want it to post right now.','comiceasel').'</li>';
		$output .= '<li>'.__('Set the featured image as the comic.&nbsp;  You can find the link to press to do this in right column in this editor.&nbsp;  After it uploads, click the [use as featured image]','comiceasel').'<br />';
		$output .= '<li>'.__('Set the comic into a chapter, all comics must be in a chapter.&nbsp;  If you do not have one, make one.','comiceasel').'</li>';
		$output .= '<li>'.__('Tag characters in the comic and location the comic takes place (not required).','comiceasel').'</li>';
		$output .= '<li>'.__('Click the [screen options] in the upper right corner of the editor and enable [x] Discussion if it is not (one time only).&nbsp;  So you can enable/disable commenting.','comiceasel').'</li>';
		$output .= '<li>'.__('Publish.','comiceasel').'</li>';
		$output .= '</ol></ul>';
		$output .= __('You can move the editor boxes around.&nbsp;  Drag them to where it would suit your individual taste in where you want them.','comiceasel').'<br /><br />';
		$output .= __('Minimize these directions by clicking the title of the box.','comiceasel');
		echo $output;
?>
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
			<input id="comic-open-lightbox" name="comic-open-lightbox" type="checkbox" value="1" <?php checked(1, get_post_meta( $post->ID, 'comic-open-lightbox', true )); ?> />
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="comic-has-map"><?php _e('*Comic has Map?','comiceasel'); ?></label></th>
		<td>
			<input id="comic-has-map" name="comic-has-map" type="checkbox" value="1" <?php checked(1, get_post_meta( $post->ID, 'comic-has-map', true )); ?> />
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="comic-content-warning"><?php _e('Enable Content Warning?','comiceasel'); ?></label></th>
		<td>
			<input id="comic-content-warning" name="comic-content-warning" type="checkbox" value="1" <?php checked(1, get_post_meta( $post->ID, 'comic-content-warning', true )); ?> />
		</td>
	</tr>	
</table>
<em><?php _e('*  usemap="#comicmap" will be added, add the map html to the below html box','comiceasel'); ?></em>
<?php
}

function ceo_media_embed_box($post) {
	$media_url = get_post_meta($post->ID, 'media_url', true);
	$media_width = get_post_meta($post->ID, 'media_width', true);
	if (empty($media_url)) $media_url = '';
	if (empty($media_width)) {
		global $content_width;
		$media_width = $content_width;
	}
?>
You can add the url from:<br />
blip.tv, DailyMotion, FunnyOrDie.com, Hulu, Instagram, Qik, Photobucket, Rdio, Revision3, Scribd, SlideShare, Smugmug, SoundCloud, Spotify, Youtube, Twitter, Vimeo, WordPress.tv<br />
<em>You still need to add a featured image to be used as the thumbnail.</em><br />
	<input id="media_url" name="media_url" type="input" style="width: 80%" value="<?php echo $media_url; ?>" /><br />
	Width to use (default is global $content_width): <input id="media_width" name="media_width" type="input" style="width: 100px;" value="<?php echo $media_width; ?>" /> 
<?php
}

function ceo_edit_linkto_in_post($post) {
	$linkto_url = get_post_meta($post->ID, 'link-to', true); 
	_e('Add url here or leave empty to use next comic or default none', 'comiceasel');
	?>
	<br />
	<input id="link-to" name="link-to" type="input" style="width: 80%" value="<?php echo $linkto_url; ?>" /><br />
	<?php
}

function ceo_edit_refer_only_in_post($post) {
	$refer_only = get_post_meta($post->ID, 'refer-only', true); 
	$refer_only_msg = get_post_meta($post->ID, 'refer-only-msg', true);
	_e('Add url here of referring website to make it only visible when coming from that url.', 'comiceasel');
	?>
	<br />
	<input id="refer-only" name="refer-only" type="input" style="width: 80%" value="<?php echo $refer_only; ?>" /><br />
	<br />
	<?php _e("Message to display to users who don't visit from the referring site.",'comiceasel'); ?><br />
	<input id="refer-only-msg" name="refer-only-msg" type="input" style="width: 80%" value="<?php echo $refer_only_msg; ?>" /><br />
	<?php
}

function ceo_edit_hovertext_in_post($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
	$hovertext = esc_attr( get_post_meta( $post->ID, 'comic-hovertext', true ) );
	if (empty($hovertext)) $hovertext = esc_attr( get_post_meta($post->ID, 'hovertext', true));
	if (!$hovertext) $hovertext = '';
?>
	<?php _e('The text placed here will appear when you mouse over the comic.','comiceasel'); ?><br />
	<textarea name="comic-hovertext" id="comic-hovertext" class="admin-comic-hovertext" style="width:100%"><?php echo $hovertext; ?></textarea>
<?php
}

function ceo_edit_transcript_in_post($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
?>
	<?php _e('The text placed here will appear as the transcript.','comiceasel'); ?><br />
	<textarea name="transcript" id="transcript" class="admin-transcript" style="width:100%; height: 100px;"><?php echo get_post_meta($post->ID, 'transcript', true); ?></textarea>
<?php
}

function ceo_edit_html_above_comic($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
?>
	<?php _e('The html placed here will appear above the comic.','comiceasel'); ?><br />
	<textarea name="comic-html-above" id="comic-html-above" class="admin-comic-html-above" style="width:100%"><?php echo get_post_meta( $post->ID, 'comic-html-above', true ); ?></textarea>
<?php
}

function ceo_edit_html_below_comic($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
?>
	<?php _e('The html placed here will appear below the comic.','comiceasel'); ?><br />
	<textarea name="comic-html-below" id="comic-html-below" class="admin-comic-html-below" style="width:100%"><?php echo get_post_meta( $post->ID, 'comic-html-below', true ); ?></textarea>
<?php
}

function ceo_edit_buycomic_in_post($post) { 
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
	$currentbuyprintoption = get_post_meta( $post->ID, 'buyprint-status', true );
	if (empty($currentbuyprintoption)) $currentbuyprintoption = __('Available','comiceasel');
	// backwards compatibility with comicpress
	
	$currentbuyorigoption = get_post_meta( $post->ID, 'buyorig-status', true );
	if (empty($currentbuyorigoption)) $currentbuyorigoption = __('Available','comiceasel');

	$currentbuyprintamount = get_post_meta($post->ID , 'buy_print_amount', true);
	if (empty($currentbuyprintamount)) $currentbuyprintamount = ceo_pluginfo('buy_comic_print_amount');
	$currentbuyorigamount = get_post_meta($post->ID , 'buy_print_orig_amount', true);
	if (empty($currentbuyorigamount)) $currentbuyorigamount = ceo_pluginfo('buy_comic_orig_amount');
?>
		<table>
		<tr>
		<?php if (ceo_pluginfo('buy_comic_sell_print')) { ?>
			<td align="left" valign="top" width="50%">
				<?php _e('Print Cost','comiceasel'); ?> <input name="buy_print_amount" id="buy_print_amount" type="text" size="5" value="<?php echo $currentbuyprintamount ?>" />  <br />
				<input name="buyprint-status" id="buyprint-available" type="radio" value="<?php _e('Available','comiceasel'); ?>" <?php if (($currentbuyprintoption == __('Available','comiceasel')) || empty($currentbuyprintoption)) { echo " checked"; } ?> /> <label for="buyprint-available"><?php _e('Available','comiceasel'); ?></label><br />
				<input name="buyprint-status" id="buyprint-sold" type="radio" value="<?php _e('Sold','comiceasel'); ?>" <?php if ($currentbuyprintoption == __('Sold','comiceasel')) { echo " checked"; } ?> /> <label for="buyprint-sold">Sold</label><br />
				<input name="buyprint-status" id="buyprint-outofstock" type="radio" value="<?php _e('Out of Stock','comiceasel'); ?>" <?php if ($currentbuyprintoption == __('Out of Stock','comiceasel')) { echo " checked"; } ?> /> <label for="buyprint-outofstock"><?php _e('Out of Stock','comiceasel'); ?></label><br />
				<input name="buyprint-status" id="buyprint-notavail" type="radio" value="<?php _e('Not Available','comiceasel'); ?>" <?php if ($currentbuyprintoption == __('Not Available','comiceasel')) { echo " checked"; } ?> /> <label for="buyprint-notavail"><?php _e('Not Available','comiceasel'); ?></label><br />
			</td>
		<?php }
			if (ceo_pluginfo('buy_comic_sell_original')) { ?>
			<td align="left" valign="top">
				<?php _e('Original Cost','comiceasel'); ?> <input name="buy_print_orig_amount" id="buy_print_orig_amount" size="5" type="text" value="<?php echo $currentbuyorigamount; ?>" /><br />
				<input name="buyorig-status" id="buyorig-available" type="radio" value="<?php _e('Available','comiceasel'); ?>" <?php if (($currentbuyorigoption == __('Available','comiceasel')) || empty($currentbuyorigoption)) { echo " checked"; } ?> /> <label for="buyorig-available"><?php _e('Available','comiceasel'); ?></label><br />
				<input name="buyorig-status" id="buyorig-sold" type="radio" value="<?php _e('Sold','comiceasel'); ?>" <?php if ($currentbuyorigoption == __('Sold','comiceasel')) { echo " checked"; } ?> /> <label for="buyorig-sold"><?php _e('Sold','comiceasel'); ?></label><br />
				<input name="buyorig-status" id="buyorig-notavail" type="radio" value="<?php _e('Not Available','comiceasel'); ?>" <?php if ($currentbuyorigoption == __('Not Available','comiceasel')) { echo " checked"; } ?> /> <label for="buyorig-notavail"><?php _e('Not Available','comiceasel'); ?></label><br />
			</td>
		<?php } ?>
		</tr>
		</table>
	<?php 
}

function ceo_flash_upload_box($post) { ?>
<label for="upload_flash">
    <?php _e('Enter a URL or upload a flash comic.','comiceasel'); ?><br />
    <input id="flash_file" class="flash_file" type="text" name="flash_file" value="<?php echo get_post_meta( $post->ID, 'flash_file', true ); ?>" />
    <input name="upload_flash_button" class="upload_flash_button" type="button" value="<?php _e('Upload Flash','comiceasel'); ?>" /><br />
</label>
<br />
<?php _e('Set the dimensions of the flash .swf comic.','comiceasel'); ?><br />
<label for="flash_height"><?php _e('Height:','comiceasel'); ?> <input id="flash_height" name="flash_height" type="text" value="<?php echo get_post_meta( $post->ID, 'flash_height', true ); ?>" /></label>
<label for="flash_width"><?php _e('Width:','comiceasel'); ?> <input id="flash_width" name="flash_width" type="text" value="<?php echo get_post_meta( $post->ID, 'flash_width', true ); ?>" /></label><br />
<br />
<em><?php _e('You still need to have a featured image set, it will be used as a thumbnail.','comiceasel'); ?></em>
<?php }

function ceo_edit_taxonomy_archive_overwrite() {
	global $post;
	wp_nonce_field( basename( __FILE__ ), 'comic_nonce' );
	$current_selected = get_post_meta( $post->ID, 'location-overwrite', true );
	if (!$current_selected || empty($current_selected)) $current_selected_selected = '';
	$taxonomies = array( 
		'locations',
		'characters'
	);
	$terms = get_terms($taxonomies);
	if (!is_wp_error($terms) && !empty($terms)) { ?>
		<select name="location-overwrite" id="location-overwrite">
			<option class="level-0" value="" <?php if ($current_selected == 'none' || empty($current_selected)) { ?>selected="selected"<?php } ?>><?php echo __('Do Not Use', 'comiceasel'); ?></option>
	<?php
		foreach ($terms as $term) { ?>
			<option class="level-0" value="<?php echo $term->slug; ?>" <?php if ($current_selected == $term->slug) { ?>selected="selected"<?php } ?>><?php echo $term->name; ?></option>
	<?php } ?>
		</select>
<?php		
	} else _e('There are no characters or locations yet.', 'comiceasel');
}


function ceo_add_comic_in_post() {
	add_meta_box('ceo_comic_in_post', __('Comic Directions', 'comiceasel'), 'ceo_edit_comic_in_post', 'comic', 'side', 'high');
	if ( current_theme_supports( 'post-thumbnails', 'comic' ) && post_type_supports('comic', 'thumbnail') ) {
		remove_meta_box('postimagediv', 'comic', 'side');
		add_meta_box('postimagediv', __('Set Comic/Featured Image', 'comiceasel'), 'post_thumbnail_meta_box', 'comic', 'side', 'high'); 
	}
	if (!defined('CEO_FEATURE_LINKTO'))
		add_meta_box('ceo_linkto_in_post', __('Links To', 'comiceasel'), 'ceo_edit_linkto_in_post', 'comic', 'normal', 'default');
	if (!defined('CEO_DISABLE_REFER_ONLY'))
		add_meta_box('ceo_refer_only_in_post', __('Only show this comic if it comes from referring URL?', 'comiceasel'), 'ceo_edit_refer_only_in_post', 'comic', 'normal', 'default');
	if (!defined('CEO_FEATURE_DISABLE_MISC'))
		add_meta_box('ceo_toggle_in_post', __('Misc. Comic Functionality', 'comiceasel'), 'ceo_edit_toggles_in_post', 'comic', 'side', 'low');
	if (!defined('CEO_FEATURE_DISABLE_HOVERTEXT'))
		add_meta_box('ceo_hovertext_in_post', __('Alt (Hover) Text', 'comiceasel'), 'ceo_edit_hovertext_in_post', 'comic', 'normal', 'high');
	if (!defined('CEO_FEATURE_DISABLE_TRANSCRIPT'))
		add_meta_box('ceo_transcript_in_post', __('Transcript', 'comiceasel'), 'ceo_edit_transcript_in_post', 'comic', 'normal', 'high');
	if (!defined('CEO_FEATURE_BUY_COMIC'))
		add_meta_box('ceo_buycomic_in_post', __('Buy Print/Original', 'comiceasel'), 'ceo_edit_buycomic_in_post', 'comic', 'side', 'low');
	if (!defined('CEO_FEATURE_DISABLE_HTML')) {
		add_meta_box('ceo_html_above_comic', __('HTML (Above) Comic', 'comiceasel'), 'ceo_edit_html_above_comic', 'comic', 'normal', 'high');
		add_meta_box('ceo_html_below_comic', __('HTML (Below) Comic', 'comiceasel'), 'ceo_edit_html_below_comic', 'comic', 'normal', 'high');
	}
	if (!defined('CEO_FEATURE_FLASH_UPLOAD'))
		add_meta_box('ceo_flash_upload', __('Add Flash Comic', 'comiceasel'), 'ceo_flash_upload_box', 'comic', 'normal', 'high');
	if (!defined('CEO_FEATURE_MEDIA_EMBED')) 
		add_meta_box('ceo_media_embed_file', __('Media Url as Comic', 'comiceasel'), 'ceo_media_embed_box', 'comic', 'normal', 'low');
	if (function_exists('comicpress_themeinfo'))
		add_meta_box('ceo_taxonomy_archive_overwrite', __('Use as an information page?', 'comiceasel'), 'ceo_edit_taxonomy_archive_overwrite', 'page', 'side', 'low');
	$context_output = '<ul><ol>';
	$context_output .= '<li>'.__('Add a title to the comic.&nbsp; Titles must be alpha-numerical, not just numbers.','comiceasel').'</li>';
	$context_output .= '<li>'.__('Add some info to the blog section of the comic if you want to (not required).','comiceasel').'</li>';
	$context_output .= '<li>'.__('Set the date/time you want it to be published, leave it as Publish Immediately if you want it to post right now.','comiceasel').'</li>';
	$context_output .= '<li>'.__('Set the featured image as the comic.&nbsp;  You can find the link to press to do this in right column in this editor.&nbsp;  After it uploads, click the [use as featured image]','comiceasel').'<br />';
	$context_output .= '<li>'.__('Set the comic into a chapter, all comics must be in a chapter.&nbsp;  If you do not have one, make one.','comiceasel').'</li>';
	$context_output .= '<li>'.__('Tag characters in the comic and location the comic takes place (not required).','comiceasel').'</li>';
	$context_output .= '<li>'.__('Click the [screen options] in the upper right corner of the editor and enable [x] Discussion if it is not (one time only).&nbsp;  So you can enable/disable commenting.','comiceasel').'</li>';
	$context_output .= '<li>'.__('Publish.','comiceasel').'</li>';
	$context_output .= '</ol></ul>';
	$context_output .= __('You can move the editor boxes around.&nbsp;  Drag them to where it would suit your individual taste in where you want them.','comiceasel').'<br /><br />';
	$context_output .= __('Minimize these directions by clicking the title of the box.','comiceasel');
	get_current_screen()->add_help_tab( array(
				'id'      => 'my-help-id',
				'title'   => __( 'Instructions','comiceasel' ),
				'content' => $context_output,
			) );
	add_action('admin_footer', 'ceo_change_chapter_to_radio');
	if (!defined('CEO_FEATURE_DISABLE_TEST_FOR_ERRORS') && !defined('CEO_FEATURE_DISABLE_REWRITE_RULES') && !ceo_pluginfo('disable_cal_rewrite_rules'))
		add_action('admin_notices', 'ceo_test_for_errors');
}

function ceo_test_for_errors() {
	global $post; 
	if (is_numeric($post->post_name)) { ?>
	<div class="error">
		<h2><?php _e('Problem.','comiceasel'); ?></h2>
		<?php echo __('The slug for this comic post "','comiceasel').$post->post_name.__('" is numerical.  It needs to have a letter or character added to it to not be recognized as a date.  P.S. Cannot use these characters: !&#@','comiceasel'); ?><br />
	</div>
<?php }
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
			'comic-gallery-jquery',
			'buyprint-status',
			'buyorig-status',
			'flash_file',
			'flash_height',
			'flash_width',
			'media_url',
			'media_width',
			'link-to',
			'refer-only',
			'comic-has-map',
			'location-overwrite',
			'comic-content-warning'
			);
			
	$defaultorigamount = ceo_pluginfo('buy_comic_orig_amount');
	$defaultbuyprintamount = ceo_pluginfo('buy_comic_print_amount');
	
	if (isset($_POST['buy_print_amount'])) { 
		$buyprint = esc_textarea($_POST['buy_print_amount']);
		if ($buyprint !== $defaultbuyprintamount) $meta_array[] = 'buy_print_amount';
	}
	
	if (isset($_POST['buy_print_orig_amount'])) { 
		$buyorig = esc_textarea($_POST['buy_print_orig_amount']);
		if ($buyorig !== $defaultorigamount) $meta_array[] = 'buy_print_orig_amount';
	}
			
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
