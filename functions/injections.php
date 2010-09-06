<?php

// Injected with a poison.
add_action('easel-post-foot', 'ceo_display_edit_link');
	
function ceo_display_edit_link() {
	global $post;
	if ($post->post_type == 'comic') {
		edit_post_link(__('<br />Edit Comic.','comiceasel'), '', ''); 
	}
}

add_filter('easel_display_post_category', 'ceo_edit_post_category');

// TODO: Make this actually output a chapter set that the comic is in, instead of the post-type
function ceo_edit_post_category($post_category) {
	global $post;
	if ($post->post_type == 'comic') {
		$post_category = str_replace('Posted In', 'Chapter', $post_category);
		$post_category = str_replace('post-cat', 'chapter-cat', $post_category);
	}
	return $post_category;
}

add_action('easel-content-area', 'ceo_display_comic_area');

function ceo_display_comic_area() {
	global $wp_query;
	if (function_exists('easel_display_post') && !is_home()) { // If the theme isnt installed, just don't go there.
		if (is_single()) {
			ceo_display_comic_wrapper();
		} else {
			if (is_home() && !is_paged())  {
				Protect();
				$wp_query->in_the_loop = true; $comicFrontpage = new WP_Query(); $comicFrontpage->query('post_type=comic&showposts=1');
				while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
					echo "On Home Page<br />";
					ceo_display_comic_wrapper();
				endwhile;
				UnProtect();
			}
		}
	}
}

// This is used inside ceo_display_comic_area()
function ceo_display_comic_wrapper() {
	global $post; 
	if ($post->post_type == 'comic') {
		?>
		<div id="comic-wrap">
			<div id="comic-head"></div>
			<div id="comic">
				<?php the_title(); ?>
			</div>
			<div id="comic-foot"></div>
		</div>
	<?php }
}

// add_action('easel-narrowcolumn-area', 'ceo_display_comic_post_home');

function ceo_display_comic_post_home() { 
	global $wp_query;
	if (is_home() && function_exists('easel_display_post')) { 
		if (!is_paged())  {
			$wp_query->in_the_loop = true; $comicFrontpage = new WP_Query(); $comicFrontpage->query('post_type=comic&showposts=1');
			while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
				easel_display_post();
			endwhile;
		?>
		<div id="blogheader"></div>	
	<?php }
	}
}

?>
