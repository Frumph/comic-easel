<?php

// Injected with a poison.
add_action('easel-post-foot', 'comiceasel_display_edit_link');
	
function comiceasel_display_edit_link() {
	global $post;
	if ($post->post_type == 'comic') {
		edit_post_link(__('<br />Edit Comic.','comiceasel'), '', ''); 
	}
}

add_filter('easel_display_post_category', 'comiceasel_edit_post_category');

// TODO: Make this actually output a chapter set that the comic is in, instead of the post-type
function comiceasel_edit_post_category($post_category) {
	global $post;
	if ($post->post_type == 'comic') {
		$post_category = str_replace('Posted In', 'Chapter', $post_category);
		$post_category = str_replace('post-cat', 'chapter-cat', $post_category);
	}
	return $post_category;
}

?>
