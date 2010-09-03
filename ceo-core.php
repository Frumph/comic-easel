<?php

// actions
add_action('admin_menu', 'ceo_add_menu_pages');
add_action('wp_dashboard_setup', 'ceo_add_dashboard_widgets' );

// add_action( 'admin_notices', 'ceo_test_information' );


// INIT ComicPress Manager pages & hook activation of scripts per page.
function ceo_add_menu_pages() {
	$menu_location = 'edit.php?post_type=comic';
	$plugin_title = __('Comic Easel', 'comiceasel');
	$image_title = __('Image Manager', 'comiceasel');
	$chapter_title = __('Chapter Manager', 'comiceasel');
	$config_title = __('Config', 'comiceasel');
	
	// the ceo_pluginfo used here actually initiates it.
	$image_manager_hook = add_submenu_page($menu_location,  $plugin_title . ' - ' . $image_title, $image_title, 'edit_theme_options', 'comiceasel-image-manager', 'ceo_image_manager');
	$chapter_manager_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $chapter_title, $chapter_title, 'edit_theme_options', 'comiceasel-chapter-manager', 'ceo_chapter_manager');
	$config_hook = add_submenu_page($menu_location,   $plugin_title . ' - ' . $config_title, $config_title, 'edit_theme_options', 'comiceasel-config', 'ceo_manager_config');

	// Scripts for the chapter manager page.
	// Notice how its checking the _GET['page'], do this for the other areas
	// if you need to execute scripts on the particular areas
	switch ($_GET['page']) {
		case 'comiceasel-chapter-manager':
			add_action('admin_print_scripts-' . $chapter_manager_hook, 'ceo_load_scripts_chapter_manager');
			break;
	}
}

function ceo_load_scripts_chapter_manager() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
}

// This is done this way to *not* load pages unless they are called, self sufficient code, but since attached to the ceo-core it can use the library in core.
function ceo_image_manager() {
	require_once('ceo-image-manager.php');
}

function ceo_chapter_manager() {
	require_once('ceo-chapter-manager.php');
}

function ceo_manager_config() {
	require_once('ceo-config.php');
}

/**
 * This set of functions is for displaying the dashboard feed widget.
 *
 */
function ceo_dashboard_feed_widget() {
	wp_widget_rss_output('http://frumph.net/feed/', array('items' => 2, 'show_summary' => true));
} 

function ceo_add_dashboard_widgets() {
	wp_add_dashboard_widget('ceo_dashboard_widget', 'Frumph.NET News', 'ceo_dashboard_feed_widget');	
}

/**
 * This set of functions is to display test information on the dashboard, much like an error
 * 
 */
function ceo_test_information($var_dump_info) { ?>
	<div class="error">
	<h2><?php _e('Comic Easel - Test Information','comiceasel'); ?></h2>
	<?php var_dump(get_post_types()); ?><br />
	</div>
<?php }

// if (is_admin()) add_action( 'admin_notices', 'ceo_test_information' );

/**
 * This is function ceo_clean_filename
 *
 * @param string $filename the BASE filename
 * @return string returns the rawurlencoded filename with the %2F put back to /
 *
 */
function ceo_clean_filename($filename) {
	return str_replace("%2F", "/", rawurlencode($filename));
}

/**
 * These set of functions are for the configuration ceo_pluginfo() information
 * This needs to be updated to the CEO plugin's options and not ComicPress's
 */
function ceo_load_config($reset = false) {

	if ($reset) delete_option('comicpress-config');
	
	$cp_config = get_option('comicpress-config');
	if (empty($cp_config)) {
		delete_option('comicpress-config');
		foreach (array(
			'comiccat' => '2',
			'blogcat' => '3',
			'comic_folder' => 'comics',
			'rss_comic_folder' => 'comics-rss',
			'archive_comic_folder' => 'comics-archive',
			'mini_comic_folder' => 'comics-mini',
			'archive_comic_width' => '300',
			'rss_comic_width' => '480',
			'mini_comic_width' => '170',
			'add_dashboard_frumph_feed_widget' => true
		) as $field => $value) {
			$cp_config[$field] = $value;
		}

		add_option('comicpress-config', $cp_config, '', 'yes');
	}
	return $cp_config;
}

/**
 * These set of functions are for the configuration ceo_pluginfo() information
 * 
 */
function ceo_load_manager_config_options($reset = false) {

	if ($reset) delete_option('comiceasel-config');
	
	$ceo_config = get_option('comiceasel-config');
	if (empty($ceo_config)) {
		delete_option('comiceasel-config');
		foreach (array(
			'add_dashboard_frumph_feed_widget' => true
		) as $field => $value) {
			$ceo_config[$field] = $value;
		}

		add_option('comiceasel-config', $ceo_config, '', 'yes');
	}
	return $ceo_config;
}

function ceo_pluginfo($whichinfo = null) {
	global $ceo_plugininfo;
	if (empty($ceo_plugininfo) || $whichinfo == 'reset') {
		$comiceasel_config = ceo_load_config();
		$comiceasel_manager_config = ceo_load_manager_config_options();
		$ceo_coreinfo = wp_upload_dir();
		$ceo_addinfo = array(
				'themeurl' => get_template_directory_uri(),
				'themepath' => get_template_directory(),
				'styleurl' => get_stylesheet_directory_uri(),
				'stylepath' => get_stylesheet_directory(),
				'home' => get_option('home'),
				'wpurl' => get_bloginfo('wpurl'),
				'siteurl' => get_option('siteurl')
		);
		$ceo_plugininfo = array_merge($ceo_coreinfo, $ceo_addinfo);
		$ceo_plugininfo = array_merge($ceo_plugininfo, $comiceasel_config);
		$ceo_plugininfo = array_merge($ceo_plugininfo, $comiceasel_manager_config);
	}
	if ($whichinfo) return $ceo_plugininfo[$whichinfo];
	return $ceo_plugininfo;
}

function ceo_feed_request($requests) {
	if (isset($requests['feed']))
		$requests['post_type'] = get_post_types();
	return $requests;
}

add_filter('request', 'ceo_feed_request');


?>
