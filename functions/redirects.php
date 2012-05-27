<?php

if ( isset( $_GET['latest'] ) )
	add_action( 'template_redirect', 'ceo_latest_comic_jump' );
	
if ( isset( $_GET['random'] ) )
	add_action( 'template_redirect', 'ceo_random_comic' );

//to use simply create a URL link to "/?latestcomic"
function ceo_latest_comic_jump() {
	if (isset($_GET['latest'])) $chapter = (int)$_GET['latest'];
	if (!empty($chapter)) {
		$this_chapter = get_term_by('term_id', $chapter, 'chapters');
		$args = array( 
				'numberposts' => 1, 
				'post_type' => 'comic', 
				'orderby' => 'post_date', 
				'order' => 'DESC', 
				'post_status' => 'publish', 
				'chapters' => $this_chapter->slug
				);					
		$qposts = get_posts( $args );
		if (is_array($qposts)) {
			$qposts = reset($qposts);
			wp_redirect( get_permalink( $qposts->ID ) );
		} else {
			wp_redirect( bloginfo('url') );
		}
	} else {
		$args = array( 
				'numberposts' => 1, 
				'post_type' => 'comic', 
				'orderby' => 'post_date', 
				'order' => 'DESC', 
				'post_status' => 'publish'
				);
		$qposts = get_posts( $args );
		if (is_array($qposts)) {
			$qposts = reset($qposts);
			wp_redirect( get_permalink( $qposts->ID ) );
		}
	}
	wp_reset_query();
	exit;
}

function ceo_random_comic() {
	$args = array( 
			'numberposts' => 1, 
			'post_type' => 'comic', 
			'orderby' => 'rand', 
			'post_status' => 'publish'
			);
	$qposts = get_posts( $args );
	if (is_array($qposts)) {
		$qposts = reset($qposts);
		wp_redirect( get_permalink( $qposts->ID ) );
	}
	exit;
}

?>