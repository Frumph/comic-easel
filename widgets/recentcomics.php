<?php
/*
Widget Name: Latest Comics Widget
Widget URI: http://comiceasel.org/
Description: Display a list of links of the latest comics.
Author: Philip M. Hofer (Frumph)
Version: 1.02
*/

class ceo_recent_comics_widget extends WP_Widget {

	function ceo_recent_comics_widget() {
		$widget_ops = array('classname' => __CLASS__, 'description' => __('The most recent comics.', 'comiceasel') );
		$this->WP_Widget(__CLASS__, __('Recent Comics', 'comiceasel'), $widget_ops);
	}

	function widget($args, $instance) {
		global $post;
		extract($args, EXTR_SKIP); 
		echo $before_widget;
		$title = empty($instance['title']) ? __('Recent Comics','comiceasel') : apply_filters('widget_title', $instance['title']); 
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }; 
		$latestcomics = new WP_Query('showposts=5&numberposts=5&post_type=comic');
		if ($latestcomics->have_posts() ) {
			?>
			<ul>
			<?php while ($latestcomics->have_posts()) {  
				$latestcomics->the_post(); ?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
			<?php } ?>
			</ul>
			<?php
		}
		wp_reset_postdata();
		echo $after_widget;
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

add_action( 'widgets_init', 'ceo_recent_comics_widget_init' );

function ceo_recent_comics_widget_init() {
	register_widget('ceo_recent_comics_widget');
}


?>