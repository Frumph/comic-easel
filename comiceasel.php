<?php
/*
Plugin Name: Comic Easel
Plugin URI: http://comiceasel.com
Description: Comic Easel allows you to incorporate a WebComic using the WordPress Media Library functionality with Navigation into almost any WordPress theme. With just a few modifications of adding *injection* action locations into a theme, you can have the theme of your choice display a comic.
Version: 1.0.2
Author: Philip M. Hofer (Frumph)
Author URI: http://frumph.net/

Copyright 2012 Philip M. Hofer (Frumph)  (email : philip@frumph.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

add_action('init', 'ceo_initialize_post_types');

function ceo_initialize_post_types() {
	$labels = array(
			'name' => __('Comics', 'comiceasel'),
			'singular_name' => __('Comic', 'comiceasel'),
			'add_new' => __('Add Comic', 'comiceasel'),
			'add_new_item' => __('Add Comic', 'comiceasel'),
			'edit_item' => __('Edit Comic','comiceasel'),
			'edit' => __('Edit', 'comiceasel'),
			'new_item' => __('New Comic', 'comiceasel'),
			'view_item' => __('View Comic', 'comiceasel'),
			'search_items' => __('Search Comics', 'comiceasel'),
			'not_found' =>  __('No comics found', 'comiceasel'),
			'not_found_in_trash' => __('No comics found in Trash', 'comiceasel'), 
			'view' =>  __('View Comic', 'comiceasel'),
			'parent_item_colon' => ''
			);
	
	register_post_type(
		'comic', 
			array(
				'labels' => $labels,
				'public' => true,
				'public_queryable' => true,
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'taxonomies' => array( 'post_tag' ),
				'rewrite' => array( 'slug' => 'comic', 'with_front' => true ),
				'hierarchical' => false,
				'can_export' => true,
				'show_in_menu' => true,
				'menu_position' => 5,
				'exclude_from_search' => false,
				'has_archive' => true,
				'query_var' => true,
				'menu_icon' => ceo_pluginfo('plugin_url') . '/images/ceo-icon.png',
				'supports' => array( 'title', 'editor', 'excerpt', 'author', 'comments', 'thumbnail', 'custom-fields', 'revisions', 'trackbacks' ),
				'description' => 'Post type for Comics'
				));

	$labels = array(
			'name' => _x( 'Chapters', 'taxonomy general name' ),
			'singular_name' => _x( 'Chapter', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Chapters' ),
			'popular_items' => __( 'Popular Chapters' ),
			'all_items' => __( 'All Chapters' ),
			'parent_item' => __( 'Parent Chapter' ),
			'parent_item_colon' => __( 'Parent Chapter:' ),
			'edit_item' => __( 'Edit Chapters' ), 
			'update_item' => __( 'Update Chapters' ),
			'add_new_item' => __( 'Add New Chapter' ),
			'new_item_name' => __( 'New Chapter Name' ),
			); 	

	register_taxonomy('chapters', 'comic', array(
				'hierarchical' => true,
				'public' => true,
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'show_tagcloud' => false,
				'rewrite' => array( 'slug' => 'chapter' ),
				));

	$labels = array(
			'name' => _x('Characters', 'taxonomy general name' ),
			'singular_name' => _x( 'Character', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Characters' ),
			'popular_items' => __( 'Popular Characters' ),
			'all_items' => __( 'All Characters' ),
			'parent_item' => __( 'Parent Character' ),
			'parent_item_colon' => __( 'Parent Character:' ),
			'edit_item' => __( 'Edit Character' ), 
			'update_item' => __( 'Update Character' ),
			'add_new_item' => __( 'Add New Character' ),
			'new_item_name' => __( 'New Character Name' ),
			); 	

	register_taxonomy('characters', 'comic', array(
				'hierarchical' => false,
				'public' => true,
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'show_tagcloud' => false,
				'rewrite' => array( 'slug' => 'character' ),
				));
				
	$labels = array(
			'name' => _x( 'Locations', 'taxonomy general name' ),
			'singular_name' => _x( 'Location', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Locations' ),
			'popular_items' => __( 'Popular Locations' ),
			'all_items' => __( 'All Locations' ),
			'parent_item' => __( 'Parent Locations' ),
			'parent_item_colon' => __( 'Parent Location:' ),
			'edit_item' => __( 'Edit Location' ), 
			'update_item' => __( 'Update Location' ),
			'add_new_item' => __( 'Add New Location' ),
			'new_item_name' => __( 'New Location Name' ),
			); 	

	register_taxonomy('locations', 'comic', array(
				'hierarchical' => true,
				'public' => true,
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'show_tagcloud' => false,
				'rewrite' => array( 'slug' => 'location' ),
				));

	register_taxonomy_for_object_type('post_tag', 'comic');
	register_taxonomy_for_object_type('chapters', 'comic');
	register_taxonomy_for_object_type('characters', 'comic');
	register_taxonomy_for_object_type('locations', 'comic');

	// load the comiceasel language translations
	load_plugin_textdomain('comiceasel', false, basename( dirname( __FILE__ ) ) . '/languages');
		
}

// Create CEO Specific Sidebars regardless if they already exist.
function ceo_register_sidebars() {
	if (ceo_pluginfo('enable_comic_sidebar_locations')) {
		foreach (array(
					__('Over Comic', 'comiceasel'),
					__('Left of Comic','comiceasel'),
					__('Right of Comic', 'comiceasel'),
					__('Under Comic', 'comiceasel')				
					) as $sidebartitle) {
			register_sidebar(array(
						'name'=> $sidebartitle,
						'id' => 'ceo-sidebar-'.sanitize_title($sidebartitle),
						'description' => __('Comic Easel Sidebar Location', 'comiceasel'),
						'before_widget' => "<div id=\"".'%1$s'."\" class=\"widget ".'%2$s'."\">\r\n<div class=\"widget-head\"></div>\r\n<div class=\"widget-content\">\r\n",
						'after_widget'  => "</div>\r\n<div class=\"clear\"></div>\r\n<div class=\"widget-foot\"></div>\r\n</div>\r\n",
						'before_title'  => "<h2 class=\"widgettitle\">",
						'after_title'   => "</h2>\r\n"
						));
		}
	}
}

add_action('widgets_init', 'ceo_register_sidebars');

// THIS STUFF ONLY RUNS IN THE WP-ADMIN
if (is_admin()) {
	// only load the plugin code of we're in the administration part of WordPress.
	@require('ceo-admin.php');
	@require('functions/admin-meta.php');
} else {
	add_action('wp_print_styles', 'ceo_run_css');
}

// This style needs to be loaded on all the comic-easel pages inside ceo-core.php instead.

function ceo_run_css() {
	wp_register_style('comiceasel-style', ceo_pluginfo('plugin_url').'/css/comiceasel.css');
	wp_enqueue_style('comiceasel-style');
	if (is_active_widget('ceo_comic_navigation', false, 'ceo_comic_navigation', true)) {
		wp_register_style('comiceasel-navstyle', ceo_pluginfo('plugin_url').'/css/navstyle.css');
		wp_enqueue_style('comiceasel-navstyle');
	}	
}

// Flush Rewrite Rules & create chapters
register_activation_hook( __FILE__, 'ceo_flush_rewrite' );
register_deactivation_hook( __FILE__, 'ceo_flush_rewrite' );

function ceo_flush_rewrite() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

// This file contains functions that is used elsewhere in the plugin 
@require('functions/library.php');

// Filters that change the behavior of WordPress
@require('functions/filters.php');

// This file handles navigation of the comic
@require('functions/navigation.php');

// This file handles reading the comic from the filesystem and displaying it
@require('functions/displaycomic.php');

// This file contains the functions that are injected into the theme
@require('functions/injections.php');

// This file contains all the shortcodes for archives and cast pages
@require('functions/shortcodes.php');

// Redirects /?latest /?random etc.
@require('functions/redirects.php');

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
 * Get the path to the plugin folder.
 */
function ceo_get_plugin_path() {
	return PLUGINDIR . '/' . preg_replace('#^.*/([^\/]*)#', '\\1', dirname(plugin_basename(__FILE__)));
}

/**
 * These set of functions are for the configuration ceo_pluginfo() information
 * 
 */
function ceo_load_options($reset = false) {

	if ($reset) delete_option('comiceasel-config');
	
	$ceo_config = get_option('comiceasel-config');
	if (empty($ceo_config)) {
		delete_option('comiceasel-config');
		foreach (array(
			'add_dashboard_frumph_feed_widget' => true,
			'disable_comic_on_home_page' => false,
			'disable_comic_blog_on_home_page' => false,
			'click_comic_next' => true,
			'navigate_only_chapters' => true,
			'enable_chapter_nav' => false,
			'enable_comments_nav' => true,
			'enable_random_nav' => true,
			'enable_embed_nav' => false,
			'disable_default_nav' => false,
			'enable_comments_on_homepage' => false,
			'enable_comic_sidebar_locations' => false
		) as $field => $value) {
			$ceo_config[$field] = $value;
		}

		add_option('comiceasel-config', $ceo_config, '', 'yes');
	}
	return $ceo_config;
}

function ceo_pluginfo($whichinfo = null) {
	global $ceo_pluginfo;
//	ceo_load_options('reset');
	if (empty($ceo_pluginfo) || $whichinfo == 'reset') {
		// Important to assign pluginfo as an array to begin with.
		$ceo_pluginfo = array();
		$ceo_options = ceo_load_options();
		$ceo_coreinfo = wp_upload_dir();
		$ceo_addinfo = array(
				// if wp_upload_dir reports an error, capture it
				'error' => $ceo_coreinfo['error'],
				// upload_path-url
				'base_url' => trailingslashit($ceo_coreinfo['baseurl']),
				'base_path' => trailingslashit($ceo_coreinfo['basedir']),
				// Parent theme
				'theme_url' => get_template_directory_uri(),
				'theme_path' => get_template_directory(),
				// Child Theme (or parent if no child)
				'style_url' => get_stylesheet_directory_uri(),
				'style_path' => get_stylesheet_directory(),
				// comic-easel plugin directory/url
				'plugin_url' => plugin_dir_url(dirname (__FILE__)) . 'comic-easel',
				'plugin_path' => trailingslashit(ABSPATH) . ceo_get_plugin_path()
		);
		// Combine em.
		$ceo_pluginfo = array_merge($ceo_pluginfo, $ceo_addinfo);
		$ceo_pluginfo = array_merge($ceo_pluginfo, $ceo_options);
	}
	if ($whichinfo && isset($ceo_pluginfo[$whichinfo])) return $ceo_pluginfo[$whichinfo];
	return $ceo_pluginfo;
}

/**
 * This functions is to display test information on the dashboard, instead of dumping it out to everyone.
 * This is so that a plugin doesn't generate errors on output of the var_dump() to the end user.
 */
function ceo_test_information($vartodump) { ?>
	<div class="error">
		<h2><?php _e('Comic Easel - Test Information','comiceasel'); ?></h2>
		<?php 
			var_dump($vartodump);
		?>
	</div>
<?php }

// if (is_admin()) add_action( 'admin_notices', 'ceo_test_information' );

// Load all the widgets

foreach (glob(ceo_pluginfo('plugin_path')  . '/widgets/*.php') as $widgefile) {
	require_once($widgefile);
}

?>
