<?php
/*
*  Get a sidebar and create a generic dynamic sidebar for it, else find the sidebar-*.php in the theme/childtheme
*/

function ceo_get_sidebar($location = '') {
	if (!empty($location)) do_action($location.'-top');
	if (file_exists(get_stylesheet_directory().'/sidebar-'.$location.'.php')) {
		get_sidebar($location);
	} elseif (is_active_sidebar('ceo-sidebar-'.$location)) { ?>
		<div id="sidebar-<?php echo $location; ?>" class="sidebar">
			<?php dynamic_sidebar('ceo-sidebar-'.$location); ?>
		</div>
	<?php }
	if (!empty($location)) do_action($location.'-bottom');
}

/**
 * Protect global $post and $wp_query.
 * @param object $use_this_post If provided, after saving the current post, set up this post for template tag use.
 */
function ceo_Protect($use_this_post = null) {
	global $post, $wp_query, $__post, $__wp_query;
	if (!empty($post)) {
		$__post = $post;
	}
	if (!empty($wp_query)) {
		$__wp_query = $wp_query;
	}
	if (!is_null($use_this_post)) {
		$post = $use_this_post;
		setup_postdata($post);
	}
}

/**
 * Temporarily restore the global $post variable and set it up for use.
 */
function ceo_Restore() {
	global $post, $__post;
	$post = $__post;
	setup_postdata($post);
}

/**
 * Restore global $post and $wp_query.
 */
function ceo_Unprotect() {
	global $post, $wp_query, $__post, $__wp_query;
	if (!empty($__post)) {
		$post = $__post;
	}
	if (!empty($__wp_query)) {
		$wp_query = $__wp_query;
	}
	
	$__post = $__wp_query = null;
}

function ceo_in_comic_category() {
	global $post;
	if ($post->post_type == 'comic') return true;
	return false;
}