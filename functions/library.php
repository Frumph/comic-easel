<?php
/*
*  Get a sidebar and create a generic dynamic sidebar for it, else find the sidebar-*.php in the theme/childtheme
*/

function ceo_get_sidebar($location = '') {
	global $post;
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
function ceo_protect($use_this_post = null) {
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
function ceo_restore() {
	global $post, $__post;
	$post = $__post;
	setup_postdata($post);
}

/**
 * Restore global $post and $wp_query.
 */
function ceo_unprotect() {
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

function ceo_is_comic() {
	global $post;
	if (!is_404() && ($post->post_type == 'comic')) return true;
	return false;
}

function ceo_is_chapter($chapter = '') {
	global $post;
	if (!empty($post) && $post->post_type == 'comic') {
		$chapters = array();
		$terms = wp_get_object_terms( $post->ID, 'chapters');
		foreach ($terms as $term) {
			$chapters[] = $term->slug;
		}
		if (!empty($chapters) && in_array($chapter, $chapters)) return true;
	}
	return false;
}

function ceo_test_is_chapter_in_story($story_id = 0) {
	global $post;
	if (!empty($post) && !empty($story_id) && ($post->post_type == 'comic')) {
		$children_array = array();
		$children = get_term_children($story_id, 'chapters');
		foreach ($children as $child) {
			$children_array[] = $child->term_id;
		}
		if (!empty($children_array)) {
			// get current child ID
			$terms = wp_get_object_terms($post->ID, 'chapters');
			foreach ($terms as $term) {
				if (in_array($term->term_id, $children_array)) return true;
			}
		}
	}
	return false;
}



/**
 * This function makes it so that orderby 'menu_order' is accepted and not ignored by WordPress
 */
function ceo_apply_orderby_filter($orderby, $args) {
	if ( $args['orderby'] == 'menu_order' ) {
		return 't.menu_order';
	} else
		return $orderby;
}

add_filter('get_terms_orderby', 'ceo_apply_orderby_filter', 10, 2);


function ceo_get_referer() {
	$ref = '';
	if ( ! empty( $_REQUEST['_wp_http_referer'] ) )
		$ref = $_REQUEST['_wp_http_referer'];
	else if ( ! empty( $_SERVER['HTTP_REFERER'] ) )
		$ref = $_SERVER['HTTP_REFERER'];
	
	return $ref;
}

function ceo_content_warning() {
	return apply_filters('ceo-content-warning', __('Warning, Mature Content.','comiceasel'));
}

add_action('wp_head', 'ceo_content_warning_in_head');

function ceo_content_warning_in_head() {
?>
<script>
	var contentwarningtext = "<?php echo ceo_content_warning(); ?>";
</script>
<?php
}