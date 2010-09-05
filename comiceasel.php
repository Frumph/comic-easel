<?php
/*
Plugin Name: Comic Easel
Plugin URI: http://comiceasel.com
Description: Manage a Comic with the Easel theme.
Version: 1.0
Author: Philip M. Hofer (Frumph)
Author URI: http://frumph.net/

Copyright 2010 Philip M. Hofer (Frumph)  (email : philip@frumph.net)

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

define( 'CEASEL_PLUGINPATH', trailingslashit(home_url())  . trailingslashit(ceo_get_plugin_path()) );

add_action('init', 'ceo_initialize_post_types');

function ceo_initialize_post_types() {
	$labels = array(
			'name' => _x('Comic Easel', 'post type general name'),
			'singular_name' => _x('Comic', 'post type singular name'),
			'add_new' => _x('Add Comic Post', 'comic'),
			'add_new_item' => __('Add Comic Post'),
			'edit_item' => _x('Edit Comic','comic'),
			'edit' => _x('Edit', 'comic'),
			'new_item' => __('New Comic'),
			'view_item' => __('View Comic'),
			'search_items' => __('Search Comics'),
			'not_found' =>  __('No comics found'),
			'not_found_in_trash' => __('No comics found in Trash'), 
			'view' =>  __('View Comic'),
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
				'rewrite' => true,
				'hierarchical' => false,
				'menu_position' => 5,
				'menu_icon' => CEASEL_PLUGINPATH . 'images/ceo-icon.png',
				'supports' => array( 'title', 'editor', 'excerpt', 'author', 'comments', 'thumbnail', 'custom-fields' )
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

	register_taxonomy('chapters',array('chapter'), array(
				'hierarchical' => true,
				'public' => true,
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'show_tagcloud' => false,
				'rewrite' => array( 'slug' => 'chapter' ),
				));

	$labels = array(
			'name' => _x( 'Characters', 'taxonomy general name' ),
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

	register_taxonomy('characters',array('showcase'), array(
				'hierarchical' => false,
				'public' => true,
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'show_tagcloud' => false,
				'rewrite' => array( 'slug' => 'character' ),
				));

	register_taxonomy_for_object_type('chapters', 'comic');
	register_taxonomy_for_object_type('characters', 'comic');
}


/**
 * Get the path to the plugin folder.
 */
function ceo_get_plugin_path() {
	return PLUGINDIR . '/' . preg_replace('#^.*/([^\/]*)#', '\\1', dirname(plugin_basename(__FILE__)));
}

// THIS STUFF ONLY RUNS IN THE WP-ADMIN
if (is_admin()) {

	// check if this is a comicpress theme, if not, dont execute.
	if (strpos(get_template_directory(), 'easel')  == false) {
		if( substr( $_SERVER[ 'PHP_SELF' ], -19 ) != '/wp-admin/index.php' ) return;
		
		function ceo_no_ceo_theme() {
			$output = '<div class="error">';
			$output .= '<h2>'.__('Comic Easel Error','comiceasel').'</h2>';
			$output .= __('The current theme is not an Easel theme; Comic Easel will not load.','comiceasel').'<br />';
			$output .='<br />';
			$output .='</div>';
			echo $output;
		}
		
		add_action( 'admin_notices', 'ceo_no_ceo_theme' );
		return;
	}
	// only load the plugin code of we're in the administration part of WordPress.
	@require('ceo-core.php');
	@require('functions/admin-meta.php');
}

// Flush Rewrite Rules
register_activation_hook( __FILE__, 'ceo_flush_rewrite' );
register_deactivation_hook( __FILE__, 'ceo_flush_rewrite' );

function ceo_flush_rewrite() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

@require('functions/injections.php');

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
 * 
 */
function ceo_load_options($reset = false) {

	if ($reset) delete_option('comiceasel-config');
	
	$ceo_config = get_option('comiceasel-config');
	if (empty($ceo_config)) {
		delete_option('comiceasel-config');
		foreach (array(
			'comic_folder' => 'comics',
			'comic_folder_medium' => 'comics-medium',
			'comic_folder_small' => 'comics-archive',
			'medium_comic_width' => '360',
			'small_comic_width' => '200',
			'add_dashboard_frumph_feed_widget' => true
		) as $field => $value) {
			$ceo_config[$field] = $value;
		}

		add_option('comiceasel-config', $ceo_config, '', 'yes');
	}
	return $ceo_config;
}

function ceo_pluginfo($whichinfo = null) {
	global $ceo_pluginfo;
	if (empty($ceo_pluginfo) || $whichinfo == 'reset') {
		$ceo_pluginfo = array();
		$ceo_options = ceo_load_options('reset');
		$ceo_coreinfo = wp_upload_dir();
		// Comic folders
		$ceo_pluginfo['comic_path'] = trailingslashit($ceo_coreinfo['basedir']) . $ceo_options['comic_folder'];
		$ceo_pluginfo['comic_url'] = trailingslashit($ceo_coreinfo['baseurl']) . $ceo_options['comic_folder'];
		// Medium Thumbnail Folder
		$ceo_pluginfo['thumbnail_medium_path'] = trailingslashit($ceo_coreinfo['basedir']) . $ceo_options['comic_folder_medium'];
		$ceo_pluginfo['thumbnail_medium_url'] = trailingslashit($ceo_coreinfo['baseurl']) . $ceo_options['comic_folder_medium'];
		// Small Thumbnail Folder
		$ceo_pluginfo['thumbnail_small_path'] = trailingslashit($ceo_coreinfo['basedir']) . $ceo_options['comic_folder_small'];
		$ceo_pluginfo['thumbnail_small_url'] = trailingslashit($ceo_coreinfo['baseurl']) . $ceo_options['comic_folder_small'];
		// Combine em.
		$ceo_pluginfo = array_merge($ceo_pluginfo, $ceo_coreinfo);
		$ceo_pluginfo = array_merge($ceo_pluginfo, $ceo_options);
	}
	if ($whichinfo) return $ceo_pluginfo[$whichinfo];
	return $ceo_pluginfo;
}

function ceo_feed_request($requests) {
	if (isset($requests['feed']))
		$requests['post_type'] = get_post_types();
	return $requests;
}

add_filter('request', 'ceo_feed_request');

?>
