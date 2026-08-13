<?php
/*
Widget Name: Latest Comics Widget
Widget URI: https://github.com/Frumph/comic-easel
Description: Display a list of links of the latest comics.
Author: Philip M. Hofer (Frumph)
Version: 1.02
*/

if (!defined('ABSPATH')) exit;
	
class ceo_latest_comics_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			__CLASS__, // Base ID
			__( 'Comic Easel - Latest Comics', 'comiceasel' ), // Name
			array( 'classname' => __CLASS__, 'description' => __( 'Display a list of the latest comics.', 'comiceasel' ), ) // Args
		);
	}
	
	function widget($args, $instance) {
		global $post;
		extract($args, EXTR_SKIP); 
		echo $before_widget;
		ceo_protect();
		$title = empty($instance['title']) ? __('Latest Comics','comiceasel') : apply_filters('widget_title', $instance['title']); 
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		$args = array(
				'showposts' => (int)$instance['count'],
				'post_type' => 'comic'
				);
		$latestcomics = get_posts($args); ?>
		<ul>
		<?php foreach($latestcomics as $post) : ?>
			<li><a href="<?php echo esc_url(get_permalink($post->ID)); ?>"><?php echo ceo_title_for_html($post->ID); ?></a></li>
		<?php endforeach; ?>
		</ul>
		<?php
		echo $after_widget;
		ceo_unprotect();
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = wp_strip_all_tags($new_instance['title']);
		$instance['count'] = (int)$new_instance['count'];
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 5 ) );
		$title = wp_strip_all_tags($instance['title']);
		$count = (int)$instance['count'];
		if (($count > 50) || ($count < 1)) $count = 5;
		?>
		<p><label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:','comiceasel'); ?> <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo esc_attr($this->get_field_id('count')); ?>"><?php esc_html_e('Display Amount (1-50):','comiceasel'); ?> <input class="widefat" id="<?php echo esc_attr($this->get_field_id('count')); ?>" name="<?php echo esc_attr($this->get_field_name('count')); ?>" type="text" value="<?php echo esc_attr($count); ?>" /></label></p>
		<?php
	}
}
