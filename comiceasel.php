<?php
/*
Plugin Name: Comic Easel
Plugin URI: http://comiceasel.com
Description: Manage a Comic with the Easel theme.
Version: 1.0
Author: Philip M. Hofer (Frumph), Tyler Martin and Contributions from the ComicPress dev team.
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
// Look at the function ceo_pluginfo() at the bottom of this file for global variables used.

add_action('init', 'ceo_initialize_post_types');

function ceo_initialize_post_types() {
	$labels = array(
			'name' => __('Comic Easel', 'comiceasel'),
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
				'menu_position' => 57,
				'menu_icon' => ceo_pluginfo('plugin_url') . '/images/ceo-icon.png',
				'supports' => array( 'title', 'editor', 'excerpt', 'author', 'comments', 'thumbnail', 'custom-fields' ),
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

	register_taxonomy_for_object_type('chapters', 'comic');
	register_taxonomy_for_object_type('characters', 'comic');
	register_taxonomy_for_object_type('locations', 'comic');
}

// THIS STUFF ONLY RUNS IN THE WP-ADMIN
if (is_admin()) {
	
	// check if this is a easel theme, if not, dont execute.
	// This will be removed since actions can be added to any theme.
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
	@require('ceo-admin.php');
	@require('functions/admin-meta.php');
} else {
	// This style needs to be loaded on all the comic-easel pages inside ceo-core.php instead.
	wp_enqueue_style('comiceasel-default-style', ceo_pluginfo('plugin_url').'/css/comiceasel.css');
}

// Flush Rewrite Rules & create chapters
register_activation_hook( __FILE__, 'ceo_flush_rewrite' );
register_deactivation_hook( __FILE__, 'ceo_flush_rewrite' );

function ceo_flush_rewrite() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

// Checks chapters, creates them if needs be, checks directories, creates them if need be.
// This does not work yet, its purpose is to generate default chapters if no chapters exist.
function ceo_checkdefaults() {
	$checkchapters = get_terms('chapters', 'orderby=count&hide_empty=0');
	if (empty($checkchapters)) {
		$bookname = stripslashes(__('Book 1', 'comiceasel'));
		$bookslug = sanitize_title($bookname);
		// Should I check for .. the slug of term or name of term?
		if (!term_exists($bookslug, 'chapters')) {
			$args = array(
					'description' => stripslashes(__('The first book.', 'comiceasel')),
					'slug' => $bookslug 
					);
			$returned_book_info = wp_insert_term($bookname, 'chapters', $args);
			$parent_term_id = 0;
			// should I get term_taxonomy_id ?
			// old: if (isset($returned_book_info['term_id'])) $parent_term_id = $returned_book_info['term_id'];
			$parent_term = term_exists( $bookslug, 'chapters' ); // array is returned if taxonomy is given
			$parent_term_id = $parent_term['term_id']; // get numeric term id
			
			if ($parent_term_id) {
				$chaptername = stripslashes(__('Chapter 1', 'comiceasel'));
				$chapterslug = sanitize_title($chaptername);
				$args = array(
						'description' => stripslashes(__('First chapter of Book 1', 'comiceasel')),
						'slug' => $chapterslug,
						'parent' => $parent_term_id
						);
				$returned_chapter_info = wp_insert_term($chaptername, 'chapters', $args);
			}
		}
	}
}


// This file handles navigation of the comic
@require('functions/navigation.php');

// This file handles reading the comic from the filesystem and displaying it
@require('functions/displaycomic.php');

// This file contains the functions that are injected into the theme
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
			'comic_folder' => 'webcomics',
			'comic_folder_medium' => 'webcomics-medium',
			'comic_folder_small' => 'webcomics-small',
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
	ceo_load_options($reset);
	if (empty($ceo_pluginfo) || $whichinfo == 'reset') {
		// Important to assign pluginfo as an array to begin with.
		$ceo_pluginfo = array();
		$ceo_options = ceo_load_options('reset'); // TEMP: Reset is temporary
		$ceo_coreinfo = wp_upload_dir();
		// Child and Parent theme directories style = child(or parent), theme = parent
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
				'plugin_path' => trailingslashit(ABSPATH) . ceo_get_plugin_path(),
				// Comic folders
				'comic_url' => trailingslashit($ceo_coreinfo['baseurl']) . $ceo_options['comic_folder'],
				'comic_path' => trailingslashit($ceo_coreinfo['basedir']) . $ceo_options['comic_folder'],
				// Medium Thumbnail Folder
				'thumbnail_medium_url' => trailingslashit($ceo_coreinfo['baseurl']) . $ceo_options['comic_folder_medium'],
				'thumbnail_medium_path' => trailingslashit($ceo_coreinfo['basedir']) . $ceo_options['comic_folder_medium'],
				// Small Thumbnail Folder
				'thumbnail_small_url' =>trailingslashit($ceo_coreinfo['baseurl']) . $ceo_options['comic_folder_small'],
				'thumbnail_small_path' => trailingslashit($ceo_coreinfo['basedir']) . $ceo_options['comic_folder_small']
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
	@include($widgefile);
}

?>
