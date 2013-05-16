<?php
/*
Widget Name: Latest Comics Widget
Widget URI: http://comiceasel.org/
Description: Display a list of links of the latest comics.
Author: Philip M. Hofer (Frumph)
Version: 1.02
*/
	
class ceo_latest_comics_widget extends WP_Widget {
	
	function ceo_latest_comics_widget($skip_widget_init = false) {
		if (!$skip_widget_init) {
			$widget_ops = array('classname' => __CLASS__, 'description' => __('Display a list of the latest comics','comiceasel') );
			$this->WP_Widget(__CLASS__, __('Comic Easel - Latest Comics','comiceasel'), $widget_ops);
		}
	}
	
	function widget($args, $instance) {
		global $post;
		extract($args, EXTR_SKIP); 
		echo $before_widget;
		Protect();
		$title = empty($instance['title']) ? __('Latest Comics','comiceasel') : apply_filters('widget_title', $instance['title']); 
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		$args = array(
				'showposts' => 5,
				'post_type' => 'comic'
				);
		$latestcomics = get_posts($args); ?>
		<ul>
		<?php foreach($latestcomics as $post) : ?>
			<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
		<?php endforeach; ?>
		</ul>
		<?php
		echo $after_widget;
		UnProtect();
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','comiceasel'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<?php
	}
}

function ceo_latest_comics_widget_register() {
	register_widget('ceo_latest_comics_widget');
}

add_action('widgets_init', 'ceo_latest_comics_widget_register');

?>