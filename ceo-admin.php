<?php
if ($_REQUEST['action'] == ('ceo_uploader' || 'ceo_thumb_update' || 'ceo_comic_add' || 'ceo_comic_remove'))
	require_once('ceo-ajax-functions.php');
// actions
add_action('admin_menu', 'ceo_add_menu_pages');
add_action('wp_dashboard_setup', 'ceo_add_dashboard_widgets' );

// add_action( 'admin_notices', 'ceo_test_information' );

// INIT ComicPress Manager pages & hook activation of scripts per page.
function ceo_add_menu_pages() {
	global $pagenow, $post_type;
	
	$menu_location = 'edit.php?post_type=comic';
	$plugin_title = __('Comic Easel', 'comiceasel');
	$library_title = __('Library', 'comiceasel');
	$config_title = __('Config', 'comiceasel');
	$debug_title = __('Debug', 'comiceasel');
	
	// the ceo_pluginfo used here actually initiates it.
	$library_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $library_title, $library_title, 'edit_theme_options', 'comiceasel-library', 'ceo_library');
	$config_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $config_title, $config_title, 'edit_theme_options', 'comiceasel-config', 'ceo_manager_config');
	$debug_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $debug_title, $debug_title, 'edit_theme_options', 'comiceasel-debug', 'ceo_debug');

	// post_type is only found on the post-new.php with $_GET, so when the $pagenow is post.php it will not be able to strictly determine the post type so it will be executed on all already made post/page edits
	if ( (isset($_GET['post_type']) && $_GET['post_type'] == 'comic') || $post_type == 'comic') {
		 
	}
	// Notice how its checking the _GET['page'], do this for the other areas
	// if you need to execute scripts on the particular areas
/*	if (isset($_GET['page'])) {
		switch ($_GET['page']) {
			case 'comiceasel-chapter-manager':
			default:
				add_action('admin_print_scripts-' . $chapter_manager_hook, 'ceo_load_scripts_chapter_manager');
				break;
		}
	} */
}

function ceo_load_scripts_chapter_manager() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
}

// This is done this way to *not* load pages unless they are called, self sufficient code,
// but since attached to the ceo-admin it can use the library in core. so the global functions used in multiple areas
// go into the ceo-admin.php file, while local functions that are only run on the individual pages go on those pages
// the "forms" if there are any call the same page back up. - phil

function ceo_manager_config() {
	require_once('ceo-config.php');
}

function ceo_debug() {
	require_once('ceo-debug.php');
}

function ceo_library() {
	require_once('ceo-library.php');
}

/**
 * This set of functions is for displaying the dashboard feed widget.
 *
 */
function ceo_dashboard_feed_widget() {
	wp_widget_rss_output('http://comicpress.net/?feed=rss2', array('items' => 3, 'show_summary' => true));
} 

function ceo_add_dashboard_widgets() {
	wp_add_dashboard_widget('ceo_dashboard_widget', 'ComicPress.NET News', 'ceo_dashboard_feed_widget');	
}


?>