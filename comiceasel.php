<?php
/*
Plugin Name: Comic Easel
Plugin URI: http://comiceasel.com
Description: Comic Easel allows you to incorporate a WebComic using the WordPress Media Library functionality with Navigation into almost all WordPress themes. With just a few modifications of adding injection do_action locations into a theme, you can have the theme of your choice display and manage a webcomic.
Version: 1.12
Author: Philip M. Hofer (Frumph)
Author URI: http://frumph.net/

Copyright 2012,2013,2014 Philip M. Hofer (Frumph)  (email : philip@frumph.net)

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

	if (!post_type_exists('comic')) {
		$menu_position = 5;
		if (class_exists('Jetpack_Comic')) $menu_position = 6; /* Allow Jetpack to have 5 */
		
		$labels = array(
				'name' => __('Comics', 'comiceasel'),
				'singular_name' => __('Comic', 'comiceasel'),
				'add_new' => __('Add Comic', 'comiceasel'),
				'add_new_item' => __('Add Comic', 'comiceasel'),
				'edit_item' => __('Edit Comic','comiceasel'),
				'edit' => __('Edit', 'comiceasel'),
				'new_item' => __('New Comic', 'comiceasel'),
				'all_items' => __('All Comics', 'comiceasel'),
				'view_item' => __('View Comic', 'comiceasel'),
				'search_items' => __('Search Comics', 'comiceasel'),
				'not_found' =>  __('No comics found', 'comiceasel'),
				'not_found_in_trash' => __('No comics found in Trash', 'comiceasel'), 
				'view' =>  __('View Comic', 'comiceasel'),
				'parent_item_colon' => '',
				'menu_name' => __('Comics', 'comiceasel')
				);

		$comic_slug = ceo_pluginfo('custom_post_type_slug_name');
		
		if (empty($comic_slug) || is_array($comic_slug)) $comic_slug = 'comic';
		if (ceo_pluginfo('enable_chapter_in_url')) $comic_slug = $comic_slug.'/%chapters%';
		
		register_post_type(
			'comic', 
				array(
					'labels' => $labels,
					'public' => true,
					'public_queryable' => true,
					'show_ui' => true,
					'query_var' => 'comic', // was true
					'capability_type' => 'post',
					'taxonomies' => array( 'post_tag' ),
					'rewrite' => array( 'slug' => $comic_slug, 'with_front' => true, 'feeds' => true ),
					'hierarchical' => false,
					'can_export' => true,
					'show_in_menu' => true,
					'menu_position' => $menu_position,
					'exclude_from_search' => false,
					'map_meta_cap' => true,
					'has_archive' => true,
					'menu_icon' => ceo_pluginfo('plugin_url') . 'images/ceo-icon.png',
					'supports' => array( 'title', 'editor', 'excerpt', 'author', 'comments', 'thumbnail', 'custom-fields', 'revisions', 'trackbacks', 'shortlinks', 'publicize' ),
					/* publicize and shortlinks from jetpack plugin */
					'description' => 'Post type for Comics'
					));

		$chapter_slug = ucwords(ceo_pluginfo('chapter_type_slug_name'));
		$chapter_slug_plural = ucwords(ceo_pluginfo('chapter_type_name_plural'));
		
		if (empty($chapter_slug) || is_array($chapter_slug)) $chapter_slug = 'Chapter';
		if (empty($chapter_slug_plural) || is_array($chapter_slug_plural)) $chapter_slug_plural = 'Chapters';
		
		$labels = array(
				'name' => $chapter_slug_plural,
				'menu_name' => $chapter_slug_plural,
				'singular_name' => $chapter_slug,
				'search_items' =>  __( 'Search', 'comiceasel' ).' '.$chapter_slug_plural,
				'popular_items' => __( 'Popular', 'comiceasel' ).' '.$chapter_slug_plural,
				'all_items' => __( 'All', 'comiceasel' ).' '.$chapter_slug_plural,
				'parent_item' => __( 'Parent', 'comiceasel' ).' '.$chapter_slug,
				'parent_item_colon' => __( 'Parent', 'comiceasel' ).' '.$chapter_slug.':',
				'edit_item' => __( 'Edit', 'comiceasel' ).' '.$chapter_slug_plural, 
				'update_item' => __( 'Update', 'comiceasel' ).' '.$chapter_slug_plural,
				'add_new_item' => __( 'Add New', 'comiceasel' ).' '.$chapter_slug,
				'new_item_name' => __( 'New', 'comiceasel' ).' '.$chapter_slug.__('Name', 'comiceasel')
				);

		register_taxonomy('chapters', 'comic', array(
					'hierarchical' => true,
					'public' => true,
					'labels' => $labels,
					'show_ui' => true,
					'query_var' => true,
					'show_tagcloud' => false,
					'has_archive' => true,
					'rewrite' => array( 'slug' => strtolower($chapter_slug), 'with_front' => true, 'feeds' => true ),
					));

		$labels = array(
				'name' => __('Characters', 'comiceasel' ),
				'singular_name' => __( 'Character', 'comiceasel' ),
				'search_items' =>  __( 'Search Characters', 'comiceasel' ),
				'popular_items' => __( 'Popular Characters', 'comiceasel' ),
				'all_items' => __( 'All Characters', 'comiceasel' ),
				'parent_item' => __( 'Parent Character', 'comiceasel' ),
				'parent_item_colon' => __( 'Parent Character:', 'comiceasel' ),
				'edit_item' => __( 'Edit Character', 'comiceasel' ), 
				'update_item' => __( 'Update Character', 'comiceasel' ),
				'add_new_item' => __( 'Add New Character', 'comiceasel' ),
				'new_item_name' => __( 'New Character Name', 'comiceasel' ),
				); 	

		register_taxonomy('characters', 'comic', array(
					'hierarchical' => false,
					'public' => true,
					'labels' => $labels,
					'show_ui' => true,
					'query_var' => true,
					'show_tagcloud' => false,
					'rewrite' => array( 'slug' => 'character', 'with_front' => true, 'feeds' => false ),
					));
					
		$labels = array(
				'name' => __( 'Locations', 'comiceasel'),
				'singular_name' => __( 'Location', 'comiceasel' ),
				'search_items' =>  __( 'Search Locations', 'comiceasel' ),
				'popular_items' => __( 'Popular Locations', 'comiceasel' ),
				'all_items' => __( 'All Locations', 'comiceasel' ),
				'parent_item' => __( 'Parent Locations', 'comiceasel' ),
				'parent_item_colon' => __( 'Parent Location:', 'comiceasel' ),
				'edit_item' => __( 'Edit Location', 'comiceasel' ), 
				'update_item' => __( 'Update Location', 'comiceasel' ),
				'add_new_item' => __( 'Add New Location', 'comiceasel' ),
				'new_item_name' => __( 'New Location Name', 'comiceasel' ),
				);

		register_taxonomy('locations', 'comic', array(
					'hierarchical' => true,
					'public' => true,
					'labels' => $labels,
					'show_ui' => true,
					'query_var' => true,
					'show_tagcloud' => false,
					'rewrite' => array( 'slug' => 'location', 'with_front' => true, 'feeds' => false ),
					));
					
		if (ceo_pluginfo('allow_comics_to_have_categories')) 
			register_taxonomy_for_object_type('category', 'comic');
			
		register_taxonomy_for_object_type('post_tag', 'comic');
		register_taxonomy_for_object_type('chapters', 'comic');
		register_taxonomy_for_object_type('characters', 'comic');
		register_taxonomy_for_object_type('locations', 'comic');
	}
}

if (ceo_pluginfo('enable_chapter_in_url')) {
	add_filter( 'post_type_link', 'ceo_add_chapters_to_permalinks', 1, 2 );
}

function ceo_add_chapters_to_permalinks( $post_link, $id = 0 ){
    $post = get_post($id);
	$chapter_slug = ucwords(ceo_pluginfo('chapter_type_slug_name'));
	if (empty($chapter_slug) || is_array($chapter_slug)) $chapter_slug = 'chapter';
	$chapter_slug = strtolower($chapter_slug);
    if ( is_object( $post ) && $post->post_type == 'comic' ){
        $terms = wp_get_object_terms( $post->ID, 'chapters' );
        if( $terms ){
            return str_replace( '%chapters%' , $terms[0]->slug , $post_link );
        }
    }
    return $post_link;
}

if (!defined('CEO_FEATURE_DISABLE_REWRITE_RULES') && !ceo_pluginfo('disable_cal_rewrite_rules'))
	add_action('generate_rewrite_rules', 'ceo_datearchives_rewrite_rules');

function ceo_datearchives_rewrite_rules($wp_rewrite) {
	$rules = ceo_generate_date_archives('comic', $wp_rewrite);
	$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	return $wp_rewrite;
}

function ceo_generate_date_archives($cpt, $wp_rewrite) {
	$rules = array();

	$post_type = get_post_type_object($cpt);
	$slug_archive = $post_type->has_archive;
	if ($slug_archive === false) return $rules;
	if ($slug_archive === true) {
		$slug_archive = $post_type->name;
	}
	
	$dates = array(
		array(
			'rule' => "([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})",
			'vars' => array('year', 'monthnum', 'day')),
		array(
			'rule' => "([0-9]{4})/([0-9]{1,2})",
			'vars' => array('year', 'monthnum')),
		array(
			'rule' => "([0-9]{4})",
			'vars' => array('year'))
	);

	foreach ($dates as $data) {
		$query = 'index.php?post_type='.$cpt;
		$rule = $slug_archive.'/'.$data['rule'];

		$i = 1;
		foreach ($data['vars'] as $var) {
			$query.= '&'.$var.'='.$wp_rewrite->preg_index($i);
			$i++;
		}

		$rules[$rule."/?$"] = $query;
		$rules[$rule."/feed/(feed|rdf|rss|rss2|atom)/?$"] = $query."&feed=".$wp_rewrite->preg_index($i);
		$rules[$rule."/(feed|rdf|rss|rss2|atom)/?$"] = $query."&feed=".$wp_rewrite->preg_index($i);
		$rules[$rule."/page/([0-9]{1,})/?$"] = $query."&paged=".$wp_rewrite->preg_index($i);
	}

	return $rules;
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

function ceo_revert_filename($filename) {
	return rawurldecode($filename);
}

// THIS STUFF ONLY RUNS IN THE WP-ADMIN
if (is_admin()) {
	// only load the plugin code of we're in the administration part of WordPress.
	@require('ceo-admin.php');
	@require('functions/admin-meta.php');
} else {
	add_action('wp_print_styles', 'ceo_run_css', 1);
	add_action('wp_enqueue_scripts', 'ceo_run_scripts', 1);
}

// This style needs to be loaded on all the comic-easel pages inside ceo-core.php instead.

function ceo_run_css() {
	if (!ceo_pluginfo('disable_style_sheet')) {
		wp_register_style('comiceasel-style', ceo_pluginfo('plugin_url').'css/comiceasel.css');
		wp_enqueue_style('comiceasel-style');
		if (is_active_widget(false, false, 'ceo_comic_navigation_widget', true)) {
			if (is_child_theme() && file_exists(get_stylesheet_directory() . '/images/nav/' . ceo_pluginfo('graphic_navigation_directory') . '/navstyle.css')) {
				wp_register_style('comiceasel-navstyle',get_stylesheet_directory_uri() . '/images/nav/' . ceo_pluginfo('graphic_navigation_directory') . '/navstyle.css');
			} elseif (file_exists(get_template_directory() . '/images/nav/' . ceo_pluginfo('graphic_navigation_directory'). '/navstyle.css')) {
				wp_register_style('comiceasel-navstyle',get_template_directory_uri() . '/images/nav/' . ceo_pluginfo('graphic_navigation_directory') . '/navstyle.css');
			} elseif (file_exists(ceo_pluginfo('plugin_path') . 'images/nav/' . ceo_pluginfo('graphic_navigation_directory') . '/navstyle.css')) {
				wp_register_style('comiceasel-navstyle', ceo_pluginfo('plugin_url').'images/nav/'. ceo_pluginfo('graphic_navigation_directory'). '/navstyle.css');
			} else 
				wp_register_style('comiceasel-navstyle', ceo_pluginfo('plugin_url').'css/navstyle.css');
			wp_enqueue_style('comiceasel-navstyle');
		}
	}
}

function ceo_run_scripts() {
	global $post;
	if (!empty($post)) {
		$comic_content_warning = get_post_meta( $post->ID, 'comic-content-warning', true );
		if ($comic_content_warning) {
			wp_enqueue_script('ceo_comic_content_warning', ceo_pluginfo('plugin_url').'js/content-warning.js', null, null, true);
		}
	}
	if (!ceo_pluginfo('disable_keynav')) {
		wp_enqueue_script('ceo_keynav', ceo_pluginfo('plugin_url').'js/keynav.js', null, null, true);
	}
}

function ceo_chapters_add_menu_order_column() {
	global $wpdb;
	$init_query = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'menu_order'");
	if (!$init_query) {
		$sql = "ALTER TABLE `{$wpdb->terms}` ADD `menu_order` INT (11) NOT NULL DEFAULT 0;";
		$wpdb->query($sql);
	}
}

// currently not hooked in
function ceo_chapters_deactivate() {
	global $wpdb;
	$sql = "ALTER TABLE `{$wpdb->terms}` DROP COLUMN `menu_order`;";
	$result = $wpdb->query($sql);	
}

/**
 * If WPMS loop through and do add column to each blog, otherwise just add it to the non-wpms install
 */
function ceo_chapters_activate() {
	global $wpdb;
	if (function_exists('is_multisite') && is_multisite()) {
		$curr_blog = $wpdb->blogid;
		$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach ($blogids as $blog_id) {
			switch_to_blog($blog_id);
			ceo_chapters_add_menu_order_column();
		}
		switch_to_blog($curr_blog);
	} else
		ceo_chapters_add_menu_order_column();
}

// Flush Rewrite Rules
register_activation_hook( __FILE__, 'ceo_initialize_post_types' );
register_activation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'ceo_activation' );

function ceo_activation() {
	ceo_chapters_activate();
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
 * These set of functions are for the configuration ceo_pluginfo() information
 * 
 */
function ceo_load_options($reset = false) {

	if ($reset) delete_option('comiceasel-config');
	
	$ceo_config = get_option('comiceasel-config');
	if (empty($ceo_config)) {
		delete_option('comiceasel-config');
		foreach (array(
			'db_version' => '1.2',
			'add_dashboard_frumph_feed_widget' => true,
			'disable_comic_on_home_page' => false,
			'disable_comic_blog_on_home_page' => false,
			'click_comic_next' => true,
			'navigate_only_chapters' => false,
			'enable_chapter_nav' => false,
			'enable_comic_nav' => false,
			'enable_comment_nav' => true,
			'enable_random_nav' => true,
			'enable_embed_nav' => false,
			'disable_default_nav' => false,
			'enable_comments_on_homepage' => false,
			'enable_comic_sidebar_locations' => true,
			'thumbnail_size_for_rss' => 'thumbnail',
			'thumbnail_size_for_direct_rss' => 'full',
			'thumbnail_size_for_archive' => 'large',
			'thumbnail_size_for_facebook' => 'full',
			'graphic_navigation_directory' => 'default',
			'disable_mininav' => false,
			'include_comics_in_blog_archive' => false,
			'disable_related_comics' => true,
			'custom_post_type_slug_name' => __('comic','comiceasel'),
			'chapter_type_slug_name' => __('chapter', 'comiceasel'),
			'chapter_type_name_plural' => __('chapters', 'comiceasel'),
			'display_first_comic_on_home_page' => false,
			'disable_style_sheet' => false,
			'enable_transcripts_in_comic_posts' => false,
			'enable_chapter_only_random' => false,
			'enable_hoverbox' => false,
			'enable_buy_comic' => false,
			'buy_comic_email' => 'yourname@yourpaypalemail.com',
			'buy_comic_url' => home_url().'/shop/',
			'buy_comic_sell_print' => false,
			'buy_comic_print_amount' => '25.00',
			'buy_comic_sell_original' => true,
			'buy_comic_orig_amount' => '65.00',
			'buy_comic_text' => __('*Additional shipping charges will applied at time of purchase.','comiceasel'),
			'enable_prevnext_chapter_traversing' => false,
			'disable_cal_rewrite_rules' => false,
			'chapter_on_home' => 0,
			'allow_comics_to_have_categories' => false,
			'enable_nav_above_comic' => false,
			'enable_chapter_in_url' => false,
			'disable_keynav' => false,
			'enable_chapter_landing' => false,
			'enable_chapter_landing_first' => false,
			'enable_blog_on_chapter_landing' => false,
			'enable_comments_on_chapter_landing' => false,
			'default_nav_bar_chapter_goes_to_archive' => false,
			'remove_post_thumbnail' => false
		) as $field => $value) {
			$ceo_config[$field] = $value;
		}

		add_option('comiceasel-config', $ceo_config, '', 'yes');
	}
	return $ceo_config;
}

function ceo_pluginfo($whichinfo = null) {
	global $ceo_pluginfo;
//	ceo_load_options('reset');	-- uncomment to reset defaults
	if (empty($ceo_pluginfo) || $whichinfo == 'reset') {
		// Important to assign pluginfo as an array to begin with.
		$ceo_pluginfo = array();
		$ceo_options = ceo_load_options();
		if ( !isset($ceo_options['db_version']) || empty($ceo_options['db_version']) || (version_compare($ceo_options['db_version'], '1.2', '<')) ) {
			$ceo_options['db_version'] = '1.2';
			$ceo_options['enable_buy_comic'] = false;
			$ceo_options['buy_comic_email'] = 'yourname@yourpaypalemail.com';
			$ceo_options['buy_comic_url'] = home_url().'/shop/';
			$ceo_options['buy_comic_amount'] = '25.00';
			$ceo_options['buy_comic_sell_original'] = true;
			$ceo_options['buy_comic_sell_print'] = false;
			$ceo_options['buy_comic_orig_amount'] = '65.00';
			$ceo_options['buy_comic_text'] = __('*Additional shipping charges will applied at time of purchase.','comiceasel');
		}
		if (version_compare($ceo_options['db_version'], '1.3', '<')) {
			$ceo_options['db_version'] = '1.3';
			$ceo_options['chapter_on_home'] = 0;
		}
		if (version_compare($ceo_options['db_version'], '1.4', '<')) {
			$ceo_options['db_version'] = '1.4';
			$ceo_options['allow_comics_to_have_categories'] = false;
		}
		if (version_compare($ceo_options['db_version'], '1.5', '<')) {
			$ceo_options['db_version'] = '1.5';
			$ceo_options['enable_nav_above_comic'] = false;
		}
		if (version_compare($ceo_options['db_version'], '1.6', '<')) {
			$ceo_options['db_version'] = '1.6';
			$ceo_options['thumbnail_size_for_facebook'] = 'large';
		}
		if (version_compare($ceo_options['db_version'], '1.7', '<')) {
			$ceo_options['db_version'] = '1.7';
			$ceo_options['chapter_type_slug_name'] = 'chapter';
			$ceo_options['chapter_type_name_plural'] = 'chapters';
		}
		if (version_compare($ceo_options['db_version'], '1.8', '<')) {
			$ceo_options['db_version'] = '1.8';
			$ceo_options['disable_keynav'] = false;
			update_option('comiceasel-config', $ceo_options);
		}
		if (version_compare($ceo_options['db_version'], '1.9', '<')) {
			$ceo_options['db_version'] = '1.9';
			$ceo_options['enable_comic_nav'] = false;
			update_option('comiceasel-config', $ceo_options);
		}
		if (version_compare($ceo_options['db_version'], '1.9.6', '<')) {
			$ceo_options['db_version'] = '1.9.6';
			$ceo_options['enable_chapter_landing'] = false;
			$ceo_options['enable_chapter_landing_first'] = false;
			$ceo_options['enable_blog_on_chapter_landing'] = false;
			$ceo_options['enable_comments_on_chapter_landing'] = false;
			$ceo_options['default_nav_bar_chapter_goes_to_archive'] = false;
			update_option('comiceasel-config', $ceo_options);
		}
		if (version_compare($ceo_options['db_version'], '1.9.7', '<')) {
			$ceo_options['db_version'] = '1.9.7';
			$ceo_options['remove_post_thumbnail'] = false;
			update_option('comiceasel-config', $ceo_options);
		}
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
				'plugin_url' => plugin_dir_url(__FILE__),
				'plugin_path' => plugin_dir_path(__FILE__),
				'version' => '1.12'
		);
		// Combine em.
		$ceo_pluginfo = array_merge($ceo_pluginfo, $ceo_addinfo);
		$ceo_pluginfo = array_merge($ceo_pluginfo, $ceo_options);
		if (!isset($ceo_pluginfo['disable_style_sheet'])) $ceo_pluginfo['disable_style_sheet'] = false;
		if (!isset($ceo_pluginfo['enable_transcripts_in_comic_posts'])) $ceo_pluginfo['enable_transcripts_in_comic_posts'] = false;
		if (!isset($ceo_pluginfo['enable_prevnext_chapter_traversing'])) $ceo_pluginfo['enable_prevnext_chapter_traversing'] = false;
	}
	if ($whichinfo) {
		if (isset($ceo_pluginfo[$whichinfo])) {
			return $ceo_pluginfo[$whichinfo];
		} else return false;
	}
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

foreach (glob(ceo_pluginfo('plugin_path')  . 'widgets/*.php') as $widgefile) {
	require_once($widgefile);
}

// for older PHP versions cannot use function inside of add_action

add_action( 'widgets_init', 'ceo_register_widgets');

function ceo_register_widgets() { 
	register_widget('ceo_comic_archive_dropdown_widget');
	register_widget('ceo_casthover_reference_widget');
	register_widget('ceo_comic_blog_post_widget');
	register_widget('ceo_calendar_widget');
	register_widget('ceo_comic_list_dropdown_widget');
	register_widget('ceo_comic_navigation_widget');
	register_widget('ceo_latest_comics_widget');
	register_widget('ceo_scheduled_comics_widget');
	register_widget('ceo_thumbnail_widget');
}

function ceo_language_init() {
	load_plugin_textdomain( 'comiceasel', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}

add_action('plugins_loaded', 'ceo_language_init');

