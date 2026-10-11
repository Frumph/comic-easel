<?php
if (!defined('ABSPATH')) exit;

// actions
add_action('admin_menu', 'ceo_add_menu_pages');
add_action('admin_enqueue_scripts', 'ceo_comic_editor_scripts', 10, 1 );

function ceo_comic_editor_scripts( $hook ) {
	// The Comics list's column widths. Same screens as when this was enqueued from admin_menu:
	// the Comics list, the comic editor and the plugin's own pages under Comics.
	ceo_enqueue_admin_cpt_style('comic', 'ceo-admin-editor-style', ceo_pluginfo('plugin_url').'css/admin-editor.css', array(), ceo_pluginfo('version'));
	// On every post editor screen, as before: the meta box setup that used to queue it runs
	// for every post type.
	if ('post.php' === $hook || 'post-new.php' === $hook)
		ceo_change_chapter_to_radio();
}

/*
 * A comic belongs to exactly one chapter, so the chapter checklist on the post editor is
 * turned into radio buttons. The script is a file loaded in the footer, so it runs once the
 * checklist is in the page.
 */
function ceo_change_chapter_to_radio(){
	wp_enqueue_script('ceo-chapter-radio', ceo_pluginfo('plugin_url').'js/chapter-radio.js', array('jquery'), ceo_pluginfo('version'), true);
}

function ceo_add_menu_pages() {
	global $pagenow, $post_type;
	
	$menu_location = 'edit.php?post_type=comic';
	$plugin_title = __('Comic Easel', 'comic-easel');
	$config_title = __('Config', 'comic-easel');
	$debug_title = __('Debug', 'comic-easel');
	$import_title = __('Import', 'comic-easel');
	
	// the ceo_pluginfo used here actually initiates it.
	if (!defined('CEO_FEATURE_DISABLE_IMPORT'))
		$import_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $import_title, $import_title, 'edit_theme_options', 'comiceasel-import', 'ceo_import');	
	if (!defined('CEO_FEATURE_DISABLE_CONFIG')) {
		$config_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $config_title, $config_title, 'edit_theme_options', 'comiceasel-config', 'ceo_manager_config');
		add_action('load-' . $config_hook, 'ceo_config_page_load');
	}
	if (!defined('CEO_FEATURE_DISABLE_DEBUG'))
		$debug_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $debug_title, $debug_title, 'edit_theme_options', 'comiceasel-debug', 'ceo_debug');	
	// Add contextual help
}

function ceo_load_scripts_chapter_manager() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
}

// Fires only while the Config screen is loading, so its assets reach no other screen.
function ceo_config_page_load() {
	add_action('admin_enqueue_scripts', 'ceo_config_enqueue_scripts');
}

function ceo_config_enqueue_scripts() {
	ceo_admin_print_scripts();
	ceo_admin_print_styles();
}

function ceo_admin_print_scripts() {
	wp_enqueue_script('utils');
	wp_enqueue_script('comiceasel-config-tabs', ceo_pluginfo('plugin_url') . 'js/config-tabs.js', array('jquery'), ceo_pluginfo('version'), true);
}

/*
 * This also used to call wp_admin_css() for css/global, css/colors and css/ie. Core has not
 * shipped global.css or ie.css for years, and for a handle it does not know wp_admin_css()
 * echoes a bare <link> -- here, to a missing file. 'colors' is enqueued by core on every
 * admin screen already.
 */
function ceo_admin_print_styles() {
	wp_enqueue_style('comiceasel-options-style', ceo_pluginfo('plugin_url') . 'css/config.css', array(), ceo_pluginfo('version'));
}

// This is done this way to *not* load pages unless they are called, self sufficient code,
// but since attached to the ceo-admin it can use the library in core. so the global functions used in multiple areas
// go into the ceo-admin.php file, while local functions that are only run on the individual pages go on those pages
// the "forms" if there are any call the same page back up. - phil

function ceo_manager_config() {
	require_once __DIR__ . '/ceo-config.php';
}

function ceo_debug() {
	require_once __DIR__ . '/ceo-debug.php';
}

function ceo_import() {
	require_once __DIR__ . '/ceo-import.php';
}

function ceo_enqueue_admin_cpt_style( $cpt, $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
 
	/* Check the admin page we are on. */
	global $pagenow;
 
	/* Default to null to prevent enqueuing. */
	$enqueue = null;
 
	/* Enqueue style only if we are on the correct CPT editor page. */
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing must remain compatible with core editor URLs.
	if ( isset($_GET['post_type']) && is_scalar($_GET['post_type']) && sanitize_text_field(wp_unslash($_GET['post_type'])) == $cpt && ($pagenow == "post-new.php" || $pagenow == 'edit.php')) {
		$enqueue = true;
	}
 
	/* Enqueue style only if we are on the correct CPT editor page. */
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing must remain compatible with core editor URLs.
	if ( isset($_GET['post']) && is_scalar($_GET['post']) && ($pagenow == "post.php" || $pagenow == 'edit.php')) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing must remain compatible with core editor URLs.
		$post_id = intval(wp_unslash($_GET['post']));
		$post_obj = get_post( $post_id );
		if( is_object($post_obj) && $post_obj->post_type == $cpt )
			$enqueue = true;
	}
 
	/* Only enqueue if editor page is the correct CPT. */
	if ($enqueue) wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}
