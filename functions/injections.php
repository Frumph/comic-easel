<?php

// Injected with a poison.
add_action('easel-post-foot', 'ceo_display_edit_link');
	
function ceo_display_edit_link() {
	global $post;
	if ($post->post_type == 'comic') {
		edit_post_link(__('<br />Edit Comic.','comiceasel'), '', ''); 
	}
}

add_filter('easel_display_post_category', 'ceo_display_comic_chapters');

// TODO: Make this actually output a chapter set that the comic is in, instead of the post-type
function ceo_display_comic_chapters($post_category) {
	global $post;
	if ($post->post_type == 'comic') {
		$before = '<div class="comic-chapter">Chapter: ';
		$sep = ', '; 
		$after = '</div>';
		$post_category = get_the_term_list( $post->ID, 'chapters', $before, $sep, $after );
	}
	return apply_filters('ceo_display_comic_chapters', $post_category);
}

add_action('easel-content-area', 'ceo_display_comic_area');

function ceo_display_comic_area() {
	global $wp_query, $post;
	if (function_exists('easel_display_post')) { // If the theme isnt installed, just don't go there.
		if (is_single()) {
			ceo_display_comic_wrapper();
		} else {
			if (is_home() && !is_paged())  {
				Protect();
				$comic_args = array(
					'posts_per_page' => 1,
					'post_type' => 'comic'
				);
				$wp_query->in_the_loop = true; $comicFrontpage = new WP_Query(); $comicFrontpage->query($comic_args);
				while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
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
				<?php echo ceo_display_comic(); ?>
			</div>
			<div id="comic-foot">
				<?php ceo_display_comic_navigation(); ?>
			</div>
			<div class="clear"></div>
		</div>
	<?php }
}

add_action('easel-narrowcolumn-area', 'ceo_display_comic_post_home');

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

add_action('easel-post-info', 'ceo_display_comic_locations');

function ceo_display_comic_locations() {
	global $post;
	$before = '<div class="comic-locations">Locations: ';
	$sep = ', '; 
	$after = '</div>';
	$output = get_the_term_list( $post->ID, 'locations', $before, $sep, $after );
	echo apply_filters('ceo_display_comic_locations', $output);
}

?>
