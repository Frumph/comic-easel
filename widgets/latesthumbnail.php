<?php
/*
Widget Name: Latest Thumbnail
Description: Display a thumbnail of the latest comic.
Author: Philip M. Hofer (Frumph)
Author URI: http://frumph.net/
Version: 1.3
*/

class ceo_latest_thumbnail_widget extends WP_Widget {

	function ceo_latest_thumbnail_widget() {
		$widget_ops = array('classname' => __CLASS__, 'description' => __('Display a thumbnail of the latest comic or post, clickable to go to the comic post.', 'comiceasel') );
		$this->WP_Widget(__CLASS__, __('Latest Thumbnail', 'comiceasel'), $widget_ops);
	}
	
	function widget($args, $instance) {
		global $post, $wp_query;		
		extract($args, EXTR_SKIP);

		Protect();
		$chaptinfo = ';';
		if ($instance['thumbchapt'] !== 'All') $chaptinfo = '&chapters='.$instance['thumbchapt'];
		$comic_query = 'showposts=1&post_type=comic'.$chaptinfo;
		$posts = &query_posts($comic_query);
		$archive_image = null;
		if (have_posts()) {
			while (have_posts()) : the_post();
					echo $before_widget;
					$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']); 
					if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
					if (function_exists('has_post_thumbnail')) {
						if ( has_post_thumbnail($post->ID) ) {
							echo "<a href=\"".get_permalink($post->ID)."\" rel=\"bookmark\" title=\"Permanent Link to ".get_the_title()."\">".get_the_post_thumbnail($post->ID, 'small')."</a>\r\n";
						} else {
							echo "<a href=\"".get_permalink($post->ID)."\" title=\"".$post->post_title."\">".ceo_display_comic_thumbnail('small', $post, 198)."</a>\r\n";	
						}			
					} else { 
						echo "<a href=\"".get_permalink($post->ID)."\" title=\"".$post->post_title."\">".ceo_display_comic_thumbnail('small', $post, 198)."</a>\r\n";
					}
					echo $after_widget;
			endwhile;
		}
		UnProtect();
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['thumbchapt'] = strip_tags($new_instance['thumbchapt']);
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'thumbchapt' => '' ) );
		$title = strip_tags($instance['title']);
		$thumbchapt = $instance['thumbchapt'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'comiceasel'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><?php _e('Which Chapter?', 'comiceasel'); ?><br />	
		<?php 
		
		$allterms = &get_terms('chapters');
		if (!empty($allterms)) {
			$chaptselected = '';
			if ($thumbchapt == '') $chaptselected = 'selected';
			$chapter_options = '<option name="" '.$chaptselected.'>All</option>';
			foreach ($allterms as $term) {
				$chaptselected = '';
				if ($thumbchapt == $term->slug) $chaptselected = 'selected';
				$chapter_options .= '<option name="'.$term->slug.'" value="'.$term->slug.'" '.$chaptselected.'> '.$term->name.' </option>';
			}
			?>
			<select name=<?php echo $this->get_field_name('thumbchapt'); ?>" id="<?php echo $this->get_field_id('thumbchapt'); ?>">
			<?php echo $chapter_options; ?>
			</select>
		<?php } ?>
		</p>		
		<br />
	<?php
	}
}

add_action( 'widgets_init', 'ceo_latest_thumbnail_widget_init' );

function ceo_latest_thumbnail_widget_init() {
	register_widget('ceo_latest_thumbnail_widget');
}

?>